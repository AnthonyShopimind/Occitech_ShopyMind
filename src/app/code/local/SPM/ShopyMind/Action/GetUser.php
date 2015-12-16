<?php

class SPM_ShopyMind_Action_GetUser implements SPM_ShopyMind_Interface_Action
{
    private $params;
    private $helper;

    public function __construct($idOrEmails, $fromQuotes = false)
    {
        $this->params['identifier'] = $idOrEmails;
        $this->params['fromQuote'] = $fromQuotes;
        $this->helper = Mage::helper('shopymind');
    }

    public function process()
    {
        $users = $this->fetchUsers();
        $Formatter = new SPM_ShopyMind_DataMapper_Customer();
        $return = array_map(array($Formatter, 'format'), $users);

        return (sizeof($return) === 1 ? $return[0] : $return);
    }

    public function fetchUsers()
    {
        $tablePrefix = Mage::getConfig()->getTablePrefix();
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');

        if (is_numeric($this->params['identifier'])) {
            $query = '
                SELECT
                    `customer_default_phone`.`value` as `phone`,
                    `customer_default_billing_country`.`value` as `country_code`,
                    `customer_default_billing_state`.`code` as `region_code`,
                    `customer_default_billing_postcode`.`value` as `postcode`,
                    `customer_primary_table`.`entity_id`,
                    `customer_primary_table`.`store_id`,
                    `customer_firstname_table`.`value` as `firstname`,
                    `customer_lastname_table`.`value` as `lastname`,
                    `customer_primary_table`.`email`,
                    `customer_primary_table`.`created_at`,
                    `customer_primary_table`.`group_id`,
                    `customer_gender`.`value` AS `gender_id`,
                    `customer_birth_table`.`value` AS `birthday`
                FROM `' . $tablePrefix . 'customer_entity` AS `customer_primary_table`
                LEFT JOIN `' . $tablePrefix . 'customer_entity_datetime` AS `customer_birth_table`
                ON (
                    `customer_birth_table`.`entity_id` = `customer_primary_table`.`entity_id`
                    AND `customer_birth_table`.`attribute_id` = ' . $this->helper->getMagentoAttributeCode('customer', 'dob') . '
                )
                LEFT JOIN `' . $tablePrefix . 'customer_entity_varchar` AS `customer_firstname_table`
                ON
                    (`customer_firstname_table`.`entity_id` = `customer_primary_table`.`entity_id`)
                    AND (`customer_firstname_table`.`attribute_id` = ' . $this->helper->getMagentoAttributeCode('customer', 'firstname') . ')
                LEFT JOIN `' . $tablePrefix . 'customer_entity_varchar` AS `customer_lastname_table`
                ON
                    (`customer_lastname_table`.`entity_id` = `customer_primary_table`.`entity_id`)
                    AND (`customer_lastname_table`.`attribute_id` = ' . $this->helper->getMagentoAttributeCode('customer', 'lastname') . ')
                LEFT JOIN `' . $tablePrefix . 'customer_entity_int` AS `customer_default_billing_jt`
                ON
                    (`customer_default_billing_jt`.`entity_id` = `customer_primary_table`.`entity_id`)
                    AND (`customer_default_billing_jt`.`attribute_id` = ' . $this->helper->getMagentoAttributeCode('customer', 'default_shipping') . ')
                LEFT JOIN `' . $tablePrefix . 'customer_address_entity_varchar` AS `customer_default_billing_country`
                ON
                    (`customer_default_billing_jt`.`value` = `customer_default_billing_country`.`entity_id`)
                    AND (`customer_default_billing_country`.`attribute_id` = ' . $this->helper->getMagentoAttributeCode('customer_address', 'country_id') . ')
                LEFT JOIN `' . $tablePrefix . 'customer_address_entity_varchar` AS `customer_default_billing_postcode`
                ON
                    (`customer_default_billing_jt`.`value` = `customer_default_billing_postcode`.`entity_id`)
                    AND (`customer_default_billing_postcode`.`attribute_id` = ' . $this->helper->getMagentoAttributeCode('customer_address', 'postcode') . ')
                LEFT JOIN `' . $tablePrefix . 'customer_address_entity_varchar` AS `customer_default_phone`
                ON
                    (`customer_default_billing_jt`.`value` = `customer_default_phone`.`entity_id`)
                    AND (`customer_default_phone`.`attribute_id` = ' . $this->helper->getMagentoAttributeCode('customer_address', 'telephone') . ')
                LEFT JOIN `' . $tablePrefix . 'customer_address_entity_int` AS `customer_default_billing_state_jt`
                ON
                    (`customer_default_billing_country`.`entity_id` = `customer_default_billing_state_jt`.`entity_id`)
                    AND (
                        `customer_default_billing_state_jt`.`attribute_id` = ' . $this->helper->getMagentoAttributeCode('customer_address', 'region_id') . '
                        OR `customer_default_billing_state_jt`.`attribute_id` IS NULL
                    )
                LEFT JOIN `' . $tablePrefix . 'directory_country_region` AS `customer_default_billing_state`
                ON (`customer_default_billing_state`.`region_id` = `customer_default_billing_state_jt`.`value`)
                LEFT JOIN `' . $tablePrefix . 'customer_entity_int` AS `customer_gender`
                ON
                    (`customer_gender`.`entity_id` = `customer_primary_table`.`entity_id`)
                    AND (`customer_gender`.`attribute_id` = ' . $this->helper->getMagentoAttributeCode('customer', 'gender') . ')

                WHERE  `customer_primary_table`.`entity_id` IN(' . (implode(', ', (array)$this->params['identifier'])) . ')
                GROUP BY `customer_primary_table`.`entity_id`';
        } else {
            if ($this->params['fromQuote']) {
                $query = '
                  SELECT
                    `quote_address`.`telephone` as `phone`,
                    `quote_address`.`country_id` as `country_code`,
                    `directory_country_region`.`code` as `region_code`,
                    `quote_address`.`postcode`,
                    `quote_address`.`email` AS `entity_id`, `quote`.`store_id`,
                    `quote_address`.`firstname`,
                    `quote_address`.`lastname`,
                    `quote_address`.`email`,
                    `quote`.`created_at`,
                    `quote`.`customer_group_id` AS `group_id`,
                    `quote`.`customer_gender` AS `gender_id`,
                    `quote`.`customer_dob` AS `birthday`
                  FROM `' . $tablePrefix . 'sales_flat_quote_address` AS `quote_address`
                  INNER JOIN `' . $tablePrefix . 'sales_flat_quote` AS `quote` ON (
                    `quote`.`entity_id` = `quote_address`.`quote_id`
                  )
                  LEFT JOIN `' . $tablePrefix . 'directory_country_region` AS `directory_country_region` ON(`directory_country_region`.`region_id` = `quote_address`.`region_id`)
                  WHERE
                    `quote_address`.`email` IN("' . (implode('", "', (array)$this->params['identifier'])) . '")
                    AND `quote_address`.`address_type` = "billing"
                  GROUP BY `quote_address`.`email`
                  ORDER BY `quote`.`entity_id` ASC';
            } else {
                $query = '
                  SELECT
                    `order_address`.`telephone` as `phone`,
                    `order_address`.`country_id` as `country_code`,
                    `directory_country_region`.`code` as `region_code`,
                    `order_address`.`postcode`,
                    `order_address`.`email` AS `entity_id`,
                    `order`.`store_id`,
                    `order_address`.`firstname`,
                    `order_address`.`lastname`,
                    `order_address`.`email`,
                    `order`.`created_at`,
                    `order`.`customer_group_id` AS `group_id`,
                    `order`.`customer_gender` AS `gender_id`,
                    `order`.`customer_dob` AS `birthday`
                  FROM `' . $tablePrefix . 'sales_flat_order_address` AS `order_address`
                  INNER JOIN `' . $tablePrefix . 'sales_flat_order` AS `order` ON (
                    `order`.`entity_id` = `order_address`.`parent_id`
                  )
                  LEFT JOIN `' . $tablePrefix . 'directory_country_region` AS `directory_country_region` ON(`directory_country_region`.`region_id` = `order_address`.`region_id`)
                  WHERE
                    `order_address`.`email` IN("' . (implode('", "', (array)$this->params['identifier'])) . '")
                    AND `order_address`.`address_type` = "billing"
                  GROUP BY `order_address`.`email`
                  ORDER BY `order`.`entity_id` ASC';
            }
        }

        return $readConnection->fetchAll($query);
    }
}
