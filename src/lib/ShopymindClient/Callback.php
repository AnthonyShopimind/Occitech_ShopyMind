<?php
/**
 * Callback
 *
 * @package     ShopymindClient
 * @copyright   Copyright (c) 2013 - IDVIVE SARL (http://www.idvive.com)
 * @license     New BSD license (http://license.idvive.com)
 * @author      Yannick Dalbin <yannick@idvive.com>
 * @version     $Id Callback.php 2014-12-17$
 */
if (! in_array('Mage', get_declared_classes())) {
    require_once dirname(__FILE__) . '/../../app/Mage.php';
    umask(0);
    Mage::app();
}
if (file_exists(Mage::getBaseDir('base') . '/lib/ShopymindClient/callback_override.php'))
    require_once (Mage::getBaseDir('base') . '/lib/ShopymindClient/callback_override.php');
class ShopymindClient_Callback {
    protected static $appEmulation = false;
    protected static $initialEnvironmentInfo = false;

    /**
     * Permet de récupérer les informations utilisateur(s)
     * en fonction de son identifiant
     *
     * @param mixed|array $id
     * @return array
     */
    public static function getUser($id, $fromQuotes = false) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        $return = array ();
        $tablePrefix = Mage::getConfig()->getTablePrefix();
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        if (is_numeric($id)) {
            $query = 'SELECT `customer_default_phone`.`value` as `phone`,`customer_default_billing_country`.`value` as `country_code`,`customer_primary_table`.`entity_id`, `customer_primary_table`.`store_id`, `customer_firstname_table`.`value` as `firstname`, `customer_lastname_table`.`value` as `lastname`, `customer_primary_table`.`email`, `customer_primary_table`.`created_at`, `customer_primary_table`.`group_id`, `customer_gender`.`value` AS `gender_id`, `customer_birth_table`.`value` AS `birthday`
	         FROM `' . $tablePrefix . 'customer_entity` AS `customer_primary_table`
	         LEFT JOIN `' . $tablePrefix . 'customer_entity_datetime` AS `customer_birth_table` ON(`customer_birth_table`.`entity_id` = `customer_primary_table`.`entity_id` AND `customer_birth_table`.`attribute_id` = ' . self::getMagentoAttributeCode('customer', 'dob') . ')
	         LEFT JOIN `' . $tablePrefix . 'customer_entity_varchar` AS `customer_firstname_table` ON(`customer_firstname_table`.`entity_id` = `customer_primary_table`.`entity_id`) AND (`customer_firstname_table`.`attribute_id` = ' . self::getMagentoAttributeCode('customer', 'firstname') . ')
	         LEFT JOIN `' . $tablePrefix . 'customer_entity_varchar` AS `customer_lastname_table` ON(`customer_lastname_table`.`entity_id` = `customer_primary_table`.`entity_id`) AND (`customer_lastname_table`.`attribute_id` = ' . self::getMagentoAttributeCode('customer', 'lastname') . ')
	         LEFT JOIN `' . $tablePrefix . 'customer_entity_int` AS `customer_default_billing_jt` ON (`customer_default_billing_jt`.`entity_id` = `customer_primary_table`.`entity_id`) AND (`customer_default_billing_jt`.`attribute_id` = ' . self::getMagentoAttributeCode('customer', 'default_shipping') . ')
	         LEFT JOIN `' . $tablePrefix . 'customer_address_entity_varchar` AS `customer_default_billing_country` ON (`customer_default_billing_jt`.`value` = `customer_default_billing_country`.`entity_id`) AND (`customer_default_billing_country`.`attribute_id` = ' . self::getMagentoAttributeCode('customer_address', 'country_id') . ')
	         LEFT JOIN `' . $tablePrefix . 'customer_address_entity_varchar` AS `customer_default_phone` ON (`customer_default_billing_jt`.`value` = `customer_default_phone`.`entity_id`) AND (`customer_default_phone`.`attribute_id` = ' . self::getMagentoAttributeCode('customer_address', 'telephone') . ')
	         LEFT JOIN `' . $tablePrefix . 'customer_address_entity_int` AS `customer_default_billing_state_jt` ON (`customer_default_billing_country`.`entity_id` = `customer_default_billing_state_jt`.`entity_id`) AND (`customer_default_billing_state_jt`.`attribute_id` = ' . self::getMagentoAttributeCode('customer_address', 'region_id') . ' OR `customer_default_billing_state_jt`.`attribute_id` IS NULL)
	         LEFT JOIN `' . $tablePrefix . 'directory_country_region` AS `customer_default_billing_state` ON(`customer_default_billing_state`.`region_id` = `customer_default_billing_state_jt`.`value`)
	         LEFT JOIN `' . $tablePrefix . 'customer_entity_int` AS `customer_gender` ON(`customer_gender`.`entity_id` = `customer_primary_table`.`entity_id`) AND (`customer_gender`.`attribute_id` = ' . self::getMagentoAttributeCode('customer', 'gender') . ')
	        WHERE  `customer_primary_table`.`entity_id` IN(' . (implode(', ', (array) $id)) . ')
	        GROUP BY `customer_primary_table`.`entity_id`';
        } else {

            if ($fromQuotes) {
                $query = 'SELECT `quote_address`.`telephone` as `phone`,`quote_address`.`country_id` as `country_code`,`quote_address`.`email` AS `entity_id`, `quote`.`store_id`, `quote_address`.`firstname`, `quote_address`.`lastname`, `quote_address`.`email`, `quote`.`created_at`, `quote`.`customer_group_id` AS `group_id`, `quote`.`customer_gender` AS `gender_id`, `quote`.`customer_dob` AS `birthday`
		         FROM `' . $tablePrefix . 'sales_flat_quote_address` AS `quote_address`
		         INNER JOIN `' . $tablePrefix . 'sales_flat_quote` AS `quote` ON(`quote`.`entity_id` = `quote_address`.`quote_id`)
		        WHERE  `quote_address`.`email` IN("' . (implode('", "', (array) $id)) . '") AND `quote_address`.`address_type` = "billing"
		        GROUP BY `quote_address`.`email`
		        ORDER BY `quote`.`entity_id` ASC';
            } else {
                $query = 'SELECT `order_address`.`telephone` as `phone`,`order_address`.`country_id` as `country_code`,`order_address`.`email` AS `entity_id`, `order`.`store_id`, `order_address`.`firstname`, `order_address`.`lastname`, `order_address`.`email`, `order`.`created_at`, `order`.`customer_group_id` AS `group_id`, `order`.`customer_gender` AS `gender_id`, `order`.`customer_dob` AS `birthday`
		         FROM `' . $tablePrefix . 'sales_flat_order_address` AS `order_address`
		         INNER JOIN `' . $tablePrefix . 'sales_flat_order` AS `order` ON(`order`.`entity_id` = `order_address`.`parent_id`)
		        WHERE  `order_address`.`email` IN("' . (implode('", "', (array) $id)) . '") AND `order_address`.`address_type` = "billing"
		        GROUP BY `order_address`.`email`
		        ORDER BY `order`.`entity_id` ASC';
            }
        }
        $results = $readConnection->fetchAll($query);
        if ($results && is_array($results) && sizeof($results)) {
            foreach ( $results as $row ) {
                $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($row ['email']);
                $return [] = array (
                        'id_customer' => $row ['entity_id'],
                        'optin' => $subscriber->isSubscribed(),
                        'customer_since' => $row ['created_at'],
                        'last_name' => $row ['lastname'],
                        'first_name' => $row ['firstname'],
                        'email_address' => $row ['email'],
                        'phone1' => (isset($row ['phone']) && $row ['phone'] ? $row ['phone'] : ''),
                        'phone2' => '',
                        'gender' => (isset($row ['gender_id']) && ($row ['gender_id'] == 1 || $row ['gender_id'] == 2) ? $row ['gender_id'] : 0),
                        'birthday' => (isset($row ['birthday']) ? $row ['birthday'] : 0),
                        'locale' => self::getUserLocale($row ['entity_id'], $row ['store_id'], $row ['country_code']),
                        'date_last_order' => self::getDateLastOrder($row ['entity_id']),
                        'nb_order' => self::countCustomerOrder($row ['entity_id']),
                        'sum_order' => self::sumCustomerOrder($row ['entity_id']),
                        'groups' => array (
                                $row ['group_id']
                        )
                );
            }
        }
        return (sizeof($return) === 1 ? $return [0] : $return);
    }

    /**
     * Récupère la liste des status de commandes différents
     *
     * @return array
     */
    public static function getOrdersStatus() {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        $return = array ();
        $mageVersion = Mage::getVersion();
        if (version_compare ($mageVersion, '1.5', '>')) {
            $results = Mage::getModel('sales/order_status')->getCollection()->addFieldToSelect('status')->addFieldToSelect('label')->getData();
        } else {
            $tablePrefix = Mage::getConfig()->getTablePrefix();
            $resource = Mage::getSingleton('core/resource');

            $readConnection = $resource->getConnection('core_read');
            $query = 'SELECT `value` as `status`, `value` as `label`
	         FROM `' . $tablePrefix . 'sales_order_entity_varchar`
	        WHERE `attribute_id` = ' . self::getMagentoAttributeCode('order_status_history', 'status') . '
	        GROUP BY `value`';
            $results = $readConnection->fetchAll($query);
        }
        if ($results && is_array($results) && sizeof($results)) {
            foreach ( $results as $row ) {
                $return [$row ['status']] = $row ['label'];
            }
        }
        return $return;
    }

    /**
     * Récupération de la liste des timezones de la boutique (des clients)
     *
     * @param string|false $lastUpdate
     * @return array
     */
    public static function getTimezones($lastUpdate) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        $return = array ();
        $tablePrefix = Mage::getConfig()->getTablePrefix();
        $resource = Mage::getSingleton('core/resource');

        $readConnection = $resource->getConnection('core_read');
        $query = 'SELECT a.`value` as `country_code`, c.`code` as `region_code`
         FROM `' . $tablePrefix . 'customer_entity` AS d
        LEFT JOIN `' . $tablePrefix . 'customer_address_entity_varchar` a ON(d.`entity_id` = a.`entity_id`)
        LEFT JOIN `' . $tablePrefix . 'customer_address_entity_int` b ON(a.`entity_id` = b.`entity_id`)
        LEFT JOIN `' . $tablePrefix . 'directory_country_region` c ON(c.`region_id` = b.`value`)
        WHERE (b.`attribute_id` = ' . self::getMagentoAttributeCode('customer_address', 'region_id') . ' OR b.`attribute_id` IS NULL) AND ( a.`attribute_id` = ' . self::getMagentoAttributeCode('customer_address', 'country_id') . ')
        ' . ($lastUpdate ? ' AND d.`updated_at` >= "' . $lastUpdate . '"' : '') . '
        GROUP BY country_code,region_code';
        $results = $readConnection->fetchAll($query);
        if ($results && is_array($results) && sizeof($results)) {
            foreach ( $results as $row ) {
                $return [] = $row;
            }
        }
        return $return;
    }

    /**
     * Récupérer la liste des clients qui fêtent leur anniversaire
     *
     * Params : $dateReference = La date de référence à prendre en compte format envoyé : Y-m-d H:i:s
     * $timezones = fuseaux horaires concernés par la demande format : array(array('country' => 'FR', 'region' => '13'), array('country' => 'US', 'region' => 'ny'))
     * $nbDays = Nombre de jours de l'envoi avant la date anniv
     * $generateVoucher = false | array('amount' => '', 'type' => '', 'nbDaysValidate' => '')
     *
     * @return array
     */
    public static function getBirthdayClients($dateReference, $timezones, $nbDays = 0, $justCount = false) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        $return = array ();
        $birthDate = date("m", strtotime($dateReference . ' + ' . $nbDays . ' days')) . '-' . date("d", strtotime($dateReference . ' + ' . $nbDays . ' days'));
        $timezonesWhere = self::generateTimezonesWhere($timezones);
        if (! $timezonesWhere)
            return false;
        $tablePrefix = Mage::getConfig()->getTablePrefix();
        $resource = Mage::getSingleton('core/resource');

        $readConnection = $resource->getConnection('core_read');
        $query = 'SELECT `customer_primary_table`.`entity_id`
         FROM `' . $tablePrefix . 'customer_entity` AS `customer_primary_table`
         INNER JOIN `' . $tablePrefix . 'customer_entity_datetime` AS `customer_birth_table` ON(`customer_birth_table`.`entity_id` = `customer_primary_table`.`entity_id` AND `customer_birth_table`.`attribute_id` = ' . self::getMagentoAttributeCode('customer', 'dob') . ')
         LEFT JOIN `' . $tablePrefix . 'customer_entity_int` AS `customer_default_billing_jt` ON (`customer_default_billing_jt`.`entity_id` = `customer_primary_table`.`entity_id`) AND (`customer_default_billing_jt`.`attribute_id` = ' . self::getMagentoAttributeCode('customer', 'default_shipping') . ')
         LEFT JOIN `' . $tablePrefix . 'customer_address_entity_varchar` AS `customer_default_billing_country` ON (`customer_default_billing_jt`.`value` = `customer_default_billing_country`.`entity_id`) AND (`customer_default_billing_country`.`attribute_id` = ' . self::getMagentoAttributeCode('customer_address', 'country_id') . ')
         LEFT JOIN `' . $tablePrefix . 'customer_address_entity_int` AS `customer_default_billing_state_jt` ON (`customer_default_billing_country`.`entity_id` = `customer_default_billing_state_jt`.`entity_id`) AND (`customer_default_billing_state_jt`.`attribute_id` = ' . self::getMagentoAttributeCode('customer_address', 'region_id') . ' OR `customer_default_billing_state_jt`.`attribute_id` IS NULL)
         LEFT JOIN `' . $tablePrefix . 'directory_country_region` AS `customer_default_billing_state` ON(`customer_default_billing_state`.`region_id` = `customer_default_billing_state_jt`.`value`)
        WHERE  DATE_FORMAT(`customer_birth_table`.`value`,"%m-%d") = "' . $birthDate . '" AND ' . $timezonesWhere . ' AND `customer_primary_table`.`is_active` = 1
        GROUP BY `customer_primary_table`.`entity_id`';
        $results = $readConnection->fetchAll($query);

        if ($results && is_array($results) && sizeof($results)) {
            foreach ( $results as $row ) {
                if (! $row ['entity_id'])
                    continue;
                $return [] = array (
                        'customer' => self::getUser($row ['entity_id'])
                );
            }
        }
        $timezonesWhere = self::generateTimezonesWhere($timezones, 'order_address', 'country_id');
        if ($timezonesWhere) {
            // Guest customer
            $query = 'SELECT `order_primary`.`customer_email`
	            FROM `' . $tablePrefix . 'sales_flat_order` AS `order_primary`
	            LEFT JOIN `' . $tablePrefix . 'sales_flat_order_address` AS `order_address` ON(`order_address`.`parent_id` = `order_primary`.`entity_id`) AND (`order_address`.`address_type` = "billing")
	            LEFT JOIN `' . $tablePrefix . 'directory_country_region` AS `customer_default_billing_state` ON(`customer_default_billing_state`.`region_id` = `order_address`.`region_id`)
	            WHERE `order_primary`.`customer_is_guest` = 1 AND DATE_FORMAT(`order_primary`.`customer_dob`,"%m-%d") = "' . $birthDate . '" AND ' . $timezonesWhere . '
	            GROUP BY `order_primary`.`customer_email`';
            $results = $readConnection->fetchAll($query);

            if ($results && is_array($results) && sizeof($results)) {
                foreach ( $results as $row ) {
                    $return [] = array (
                            'customer' => self::getUser($row ['customer_email'])
                    );
                }
            }
        }

        return ($justCount ? array (
                'count' => sizeof($return)
        ) : $return);
    }

    /**
     * Permet de récupérer les paniers abandonnés depuis un nombre de secondes
     *
     * @param int $nbSeconds
     * @param bool $justCount
     * @return array
     */
    public static function getDroppedOutCart($nbSeconds, $justCount = false) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        $return = array ();
        $date = date('Y-m-d H:i:s');
        $tablePrefix = Mage::getConfig()->getTablePrefix();
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $mageVersion = Mage::getVersion();
        $query = 'SELECT `quote_table`.* FROM `' . $tablePrefix . 'sales_flat_quote` AS `quote_table`
        LEFT JOIN `' . $tablePrefix . 'sales_flat_order` AS `order_table` ON (`order_table`.`customer_email` = `quote_table`.`customer_email` AND `order_table`.`customer_id` = `quote_table`.`customer_id` AND `order_table`.`created_at` >= DATE_SUB("' . $date . '",  INTERVAL 7 DAY) )
        WHERE (((`quote_table`.`reserved_order_id` = "" OR `quote_table`.`reserved_order_id` IS NULL)))
        AND (DATE_FORMAT(`quote_table`.`updated_at`,"%Y-%m-%d %H:%i:%s") >= DATE_SUB("' . $date . '", INTERVAL ' . ($nbSeconds * 2) . ' SECOND))
        AND (DATE_FORMAT(`quote_table`.`updated_at`,"%Y-%m-%d %H:%i:%s") <= DATE_SUB("' . $date . '", INTERVAL ' . ($nbSeconds) . ' SECOND))
        AND (`quote_table`.`items_count` > 0)
        AND (`quote_table`.`customer_id` IS NOT NULL OR `quote_table`.`customer_email` IS NOT NULL)
        AND (`order_table`.`entity_id` IS NULL)
         GROUP BY `quote_table`.`customer_email`';

        $results = $readConnection->fetchAll($query);
        if ($results && is_array($results) && sizeof($results)) {
            foreach ( $results as $row ) {
                self::startLangEmulationByStoreId($row ['store_id']);
                // if(!$row['customer_id']) continue;
                $resultProducts = Mage::getModel("sales/quote_item")->getCollection()->addFieldToFilter("quote_id", $row ['entity_id'])->addFieldToFilter("parent_item_id", array (
                        'null' => true
                ))->getData();
                if ($resultProducts && is_array($resultProducts) && sizeof($resultProducts)) {
                    $returnProducts = array ();
                    foreach ( $resultProducts as $row2 ) {
                        $product = Mage::getModel('catalog/product')->load($row2 ['product_id']);
                        try {
                            $image_url = str_replace(basename($_SERVER ['SCRIPT_NAME']) . '/', '', $product->getSmallImageUrl(200, 200));
                        } catch ( Exception $e ) {
                            $image_url = '';
                        }
                        $product_url = str_replace(basename($_SERVER ['SCRIPT_NAME']) . '/', '', $product->getProductUrl(false));
                        $returnProducts [] = array (
                                'description' => $row2 ['name'],
                                'qty' => $row2 ['qty'],
                                'price' => $row2 ['price_incl_tax'],
                                'image_url' => $image_url,
                                'product_url' => $product_url
                        );
                    }
                }
                if (sizeof($returnProducts))
                    $return [] = array (
                            'sum_cart' => ($row ['base_grand_total'] / $row ['store_to_base_rate']),
                            'currency' => $row ['base_currency_code'],
                            'tax_rate' => $row ['store_to_base_rate'],
                            'id_cart' => $row ['entity_id'],
                            'link_cart' => str_replace(basename($_SERVER ['SCRIPT_NAME']) . '/', '', Mage::getUrl('checkout/cart', array (
                                    '_nosid' => true
                            ))),
                            'articles' => $returnProducts,
                            'customer' => self::getUser(($row ['customer_id'] ? $row ['customer_id'] : $row ['customer_email']), true)
                    );
                self::stopLangEmulation();
            }
        }
        return ($justCount ? array (
                'count' => sizeof($return)
        ) : $return);
    }

    /**
     * Permet de récupérer la liste des bons clients
     * par montant de commande sur une période donnée
     *
     * @param unknown $dateReference
     * @param unknown $timezones
     * @param unknown $amount
     * @param unknown $amountMax
     * @param unknown $duration
     * @param unknown $nbDaysLastOrder
     * @param string $justCount
     * @return boolean Ambigous , multitype:multitype:Ambigous <multitype:, multitype:string , multitype:multitype:string unknown Ambigous <string, unknown> Ambigous <number, unknown> NULL multitype:unknown > >
     */
    public static function getGoodClientsByAmount($dateReference, $timezones, $amount, $amountMax, $duration, $nbDaysLastOrder, $justCount = false) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        $return = array ();
        $tablePrefix = Mage::getConfig()->getTablePrefix();
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $timezonesWhere = self::generateTimezonesWhere($timezones, 'order_address', 'country_id');
        if (! $timezonesWhere)
            return false;
        $query = 'SELECT `order_last`.`entity_id`, `order_primary`.`customer_id`, `order_primary`.`customer_email`,
            SUM((`order_primary`.`base_total_invoiced`)) AS Total
            FROM `' . $tablePrefix . 'sales_flat_order` AS `order_primary`
            LEFT JOIN `' . $tablePrefix . 'sales_flat_order` AS `order_last` ON (((`order_last`.`customer_id` IS NOT NULL AND `order_last`.`customer_id` = `order_primary`.`customer_id`) OR ((`order_last`.`customer_id` IS NULL OR `order_last`.`customer_id` = 0) AND `order_last`.`customer_email` = `order_primary`.`customer_email`)) AND DATE_FORMAT(`order_last`.`created_at`,"%Y-%m-%d %H:%i:%s") >= DATE_FORMAT(DATE_SUB("' . $dateReference . '", INTERVAL ' . (int) $nbDaysLastOrder . ' DAY),"%Y-%m-%d %H:%i:%s"))
            LEFT JOIN `' . $tablePrefix . 'sales_flat_order_address` AS `order_address` ON(`order_address`.`parent_id` = `order_primary`.`entity_id`) AND (`order_address`.`address_type` = "billing")
            LEFT JOIN `' . $tablePrefix . 'directory_country_region` AS `customer_default_billing_state` ON(`customer_default_billing_state`.`region_id` = `order_address`.`region_id`)
            WHERE DATE_FORMAT(DATE_SUB("' . $dateReference . '",INTERVAL ' . (int) $duration . ' DAY),"%Y-%m-%d %H:%i:%s") <= DATE_FORMAT(`order_primary`.`created_at`,"%Y-%m-%d %H:%i:%s")
            AND ' . $timezonesWhere . '
            AND `order_primary`.`base_total_invoiced` IS NOT NULL
            AND `order_last`.`entity_id` IS NULL
            GROUP BY `order_primary`.`customer_email`
    		HAVING `Total` >= ' . (float) $amount . '
    		' . ($amountMax ? ' AND `Total` <= ' . (float) $amountMax : '');
        $results = $readConnection->fetchAll($query);
        if ($results && is_array($results) && sizeof($results)) {
            foreach ( $results as $row ) {
                $return [] = array (
                        'customer' => self::getUser(($row ['customer_id'] ? $row ['customer_id'] : $row ['customer_email']))
                );
            }
        }
        return ($justCount ? array (
                'count' => sizeof($return)
        ) : $return);
    }

    /**
     * Permet de récupérer la liste des bons clients
     * par nombre de commandes sur une période donnée
     *
     * @param unknown $dateReference
     * @param unknown $timezones
     * @param unknown $nbOrder
     * @param unknown $nbOrderMax
     * @param unknown $duration
     * @param unknown $nbDaysLastOrder
     * @param string $justCount
     * @return boolean Ambigous , multitype:multitype:Ambigous <multitype:, multitype:string , multitype:multitype:string unknown Ambigous <string, unknown> Ambigous <number, unknown> NULL multitype:unknown > >
     */
    public static function getGoodClientsByNumberOrders($dateReference, $timezones, $nbOrder, $nbOrderMax, $duration, $nbDaysLastOrder, $justCount = false) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        $return = array ();
        $tablePrefix = Mage::getConfig()->getTablePrefix();
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $timezonesWhere = self::generateTimezonesWhere($timezones, 'order_address', 'country_id');
        if (! $timezonesWhere)
            return false;
        $query = 'SELECT `order_last`.`entity_id`, `order_primary`.`customer_id`, `order_primary`.`customer_email`,
            COUNT(`order_primary`.`entity_id`) AS Total
            FROM `' . $tablePrefix . 'sales_flat_order` AS `order_primary`
            LEFT JOIN `' . $tablePrefix . 'sales_flat_order` AS `order_last` ON (((`order_last`.`customer_id` IS NOT NULL AND `order_last`.`customer_id` = `order_primary`.`customer_id`) OR ((`order_last`.`customer_id` IS NULL OR `order_last`.`customer_id` = 0) AND `order_last`.`customer_email` = `order_primary`.`customer_email`)) AND DATE_FORMAT(`order_last`.`created_at`,"%Y-%m-%d %H:%i:%s") >= DATE_FORMAT(DATE_SUB("' . $dateReference . '", INTERVAL ' . (int) $nbDaysLastOrder . ' DAY),"%Y-%m-%d %H:%i:%s"))
            LEFT JOIN `' . $tablePrefix . 'sales_flat_order_address` AS `order_address` ON(`order_address`.`parent_id` = `order_primary`.`entity_id`) AND (`order_address`.`address_type` = "billing")
            LEFT JOIN `' . $tablePrefix . 'directory_country_region` AS `customer_default_billing_state` ON(`customer_default_billing_state`.`region_id` = `order_address`.`region_id`)
            WHERE DATE_FORMAT(DATE_SUB("' . $dateReference . '",INTERVAL ' . (int) $duration . ' DAY),"%Y-%m-%d %H:%i:%s") <= DATE_FORMAT(`order_primary`.`created_at`,"%Y-%m-%d %H:%i:%s")
            AND ' . $timezonesWhere . '
            AND `order_primary`.`base_total_invoiced` IS NOT NULL
            AND `order_last`.`entity_id` IS NULL
            GROUP BY `order_primary`.`customer_email`
    		HAVING `Total` >= ' . (int) $nbOrder . '
    		' . ($nbOrderMax ? ' AND `Total` <= ' . (int) $nbOrderMax : '');
        $results = $readConnection->fetchAll($query);

        if ($results && is_array($results) && sizeof($results)) {
            foreach ( $results as $row ) {
                $return [] = array (
                        'customer' => self::getUser(($row ['customer_id'] ? $row ['customer_id'] : $row ['customer_email']))
                );
            }
        }
        return ($justCount ? array (
                'count' => sizeof($return)
        ) : $return);
    }

    /**
     * Permet de récupérer la liste des clients n'ayants pas effectués de commandes
     * depuis un certaint temps
     *
     * @param unknown $dateReference
     * @param unknown $timezones
     * @param number $nbDays
     * @param string $relaunchOlder
     * @param string $justCount
     * @return boolean Ambigous , multitype:multitype:Ambigous <multitype:, multitype:string , multitype:multitype:string unknown Ambigous <string, unknown> Ambigous <number, unknown> NULL multitype:unknown > >
     */
    public static function getMissingClients($dateReference, $timezones, $nbDays = 0, $relaunchOlder = false, $justCount = false) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        $return = array ();

        $timezonesWhere = self::generateTimezonesWhere($timezones);
        if (! $timezonesWhere)
            return false;

        $tablePrefix = Mage::getConfig()->getTablePrefix();
        $date = date('Y-m-d 00:00:00');
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $query = 'SELECT `customer_primary_table`.`entity_id`
         FROM `' . $tablePrefix . 'customer_entity` AS `customer_primary_table`
         LEFT JOIN `' . $tablePrefix . 'sales_flat_order` AS `customer_order` ON(`customer_primary_table`.`entity_id` = `customer_order`.`customer_id`)
         LEFT JOIN `' . $tablePrefix . 'customer_entity_int` AS `customer_default_billing_jt` ON (`customer_default_billing_jt`.`entity_id` = `customer_order`.`customer_id`) AND (`customer_default_billing_jt`.`attribute_id` = ' . self::getMagentoAttributeCode('customer', 'default_shipping') . ')
         LEFT JOIN `' . $tablePrefix . 'customer_address_entity_varchar` AS `customer_default_billing_country` ON (`customer_default_billing_jt`.`value` = `customer_default_billing_country`.`entity_id`) AND (`customer_default_billing_country`.`attribute_id` = ' . self::getMagentoAttributeCode('customer_address', 'country_id') . ')
         LEFT JOIN `' . $tablePrefix . 'customer_address_entity_int` AS `customer_default_billing_state_jt` ON (`customer_default_billing_country`.`entity_id` = `customer_default_billing_state_jt`.`entity_id`) AND (`customer_default_billing_state_jt`.`attribute_id` = ' . self::getMagentoAttributeCode('customer_address', 'region_id') . ' OR `customer_default_billing_state_jt`.`attribute_id` IS NULL)
         LEFT JOIN `' . $tablePrefix . 'directory_country_region` AS `customer_default_billing_state` ON(`customer_default_billing_state`.`region_id` = `customer_default_billing_state_jt`.`value`)
        WHERE `customer_order`.`entity_id` IS NULL
        AND DATE_FORMAT(`customer_primary_table`.`created_at`,"%Y-%m-%d") = DATE_FORMAT(DATE_SUB("' . $dateReference . '", INTERVAL ' . (int) ($nbDays) . ' DAY),"%Y-%m-%d")
        AND ' . $timezonesWhere . '
         AND `customer_primary_table`.`is_active` = 1
        GROUP BY `customer_order`.`customer_id`';
        $results = $readConnection->fetchAll($query);

        if ($results && is_array($results) && sizeof($results)) {
            foreach ( $results as $row ) {
                if (! $row ['entity_id'])
                    continue;
                $return [] = array (
                        'customer' => self::getUser($row ['entity_id'])
                );
            }
        }

        return ($justCount ? array (
                'count' => sizeof($return)
        ) : $return);
    }

    /**
     * Permet de récupérer les commandes ayant un certain statut, depuis $nbDays jours
     *
     * @param unknown $dateReference
     * @param unknown $timezones
     * @param unknown $nbDays
     * @param unknown $idStatus
     * @param string $justCount
     * @return boolean Ambigous , multitype:multitype:unknown multitype:multitype:unknown string Ambigous <multitype:, multitype:string , multitype:multitype:string unknown Ambigous <string, unknown> Ambigous <number, unknown> NULL multitype:unknown > >
     */
    public static function getOrdersByStatus($dateReference, $timezones, $nbDays, $idStatus, $justCount = false) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        $return = array ();
        $tablePrefix = Mage::getConfig()->getTablePrefix();
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');

        $timezonesWhere = self::generateTimezonesWhere($timezones, 'order_address', 'country_id');
        if (! $timezonesWhere)
            return false;
        $query = 'SELECT `order_primary`.`store_id`, `order_primary`.`entity_id`, `order_primary`.`order_currency_code`, `order_primary`.`base_grand_total`, `order_primary`.`customer_id`, `order_primary`.`customer_email`
            FROM `' . $tablePrefix . 'sales_flat_order` AS `order_primary`
            LEFT JOIN `' . $tablePrefix . 'sales_flat_order` AS `order_last` ON (((`order_last`.`customer_id` IS NOT NULL AND `order_last`.`customer_id` = `order_primary`.`customer_id`) OR ((`order_last`.`customer_id` IS NULL OR `order_last`.`customer_id` = 0) AND `order_last`.`customer_email` = `order_primary`.`customer_email`)) AND `order_last`.`created_at` > `order_primary`.`created_at`)
            LEFT JOIN `' . $tablePrefix . 'sales_flat_order_status_history` AS `order_status` ON (`order_status`.`status` = `order_primary`.`status` AND `order_status`.`parent_id` = `order_primary`.`entity_id`)
            LEFT JOIN `' . $tablePrefix . 'sales_flat_order_address` AS `order_address` ON(`order_address`.`parent_id` = `order_primary`.`entity_id`) AND (`order_address`.`address_type` = "billing")
            LEFT JOIN `' . $tablePrefix . 'directory_country_region` AS `customer_default_billing_state` ON(`customer_default_billing_state`.`region_id` = `order_address`.`region_id`)
            WHERE `order_primary`.status = "' . $idStatus . '" AND DATE_FORMAT(`order_primary`.`created_at`,"%Y-%m-%d") >= DATE_FORMAT(DATE_SUB("' . $dateReference . '", INTERVAL ' . ($nbDays + 14) . ' DAY),"%Y-%m-%d")
            AND DATE_FORMAT(`order_status`.`created_at`,"%Y-%m-%d") = DATE_FORMAT(DATE_SUB("' . $dateReference . '", INTERVAL ' . ($nbDays) . ' DAY),"%Y-%m-%d")
            AND ' . $timezonesWhere . '
            AND `order_last`.`entity_id` IS NULL
            GROUP BY `order_primary`.`customer_email`';
        $results = $readConnection->fetchAll($query);

        if ($results && is_array($results) && sizeof($results)) {
            foreach ( $results as $row ) {
                self::startLangEmulationByStoreId($row ['store_id']);
                $resultProducts = Mage::getModel("sales/order_item")->getCollection()->addFieldToFilter("order_id", $row ['entity_id'])->addFieldToFilter("parent_item_id", array (
                        'null' => true
                ))->getData();
                if ($resultProducts && is_array($resultProducts) && sizeof($resultProducts)) {
                    $returnProducts = array ();
                    foreach ( $resultProducts as $row2 ) {
                        $product = Mage::getModel('catalog/product')->load($row2 ['product_id']);
                        try {
                            $image_url = str_replace(basename($_SERVER ['SCRIPT_NAME']) . '/', '', $product->getSmallImageUrl(200, 200));
                        } catch ( Exception $e ) {
                            $image_url = '';
                        }
                        $product_url = str_replace(basename($_SERVER ['SCRIPT_NAME']) . '/', '', $product->getProductUrl(false));

                        $returnProducts [] = array (
                                'description' => $row2 ['name'],
                                'qty' => $row2 ['qty_invoiced'],
                                'price' => $row2 ['price_incl_tax'],
                                'image_url' => $image_url,
                                'product_url' => $product_url
                        );
                    }
                }
                if (sizeof($returnProducts))
                    $return [] = array (
                            'currency' => $row ['order_currency_code'],
                            'total_amount' => $row ['base_grand_total'],
                            'articles' => $returnProducts,
                            'id_order' => $row ['entity_id'],
                            'customer' => self::getUser(($row ['customer_id'] ? $row ['customer_id'] : $row ['customer_email']))
                    );

                self::stopLangEmulation();
            }
        }
        return ($justCount ? array (
                'count' => sizeof($return)
        ) : $return);
    }

    /**
     * Obtention d'un shortId
     *
     * @param number $length
     * @return string
     */
    public static function shortId($length = 6) {
        $az = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $azr = rand(0, 51);
        $azs = substr($az, $azr, 10);
        $stamp = hash('sha256', time());
        $mt = hash('sha256', mt_rand(5, 20));
        $alpha = hash('sha256', $azs);
        $hash = str_shuffle($stamp . $mt . $alpha);
        $code = ucfirst(substr($hash, $azr, $length));
        return $code;
    }

    /**
     * Génération des codes de réduction
     *
     * @param array $voucherInfos
     * @param array $emails
     * @return array
     */
    public static function generateVouchers($voucherInfos, $emails) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        $vouchers = array ();
        if ($voucherInfos && is_array($voucherInfos) && $emails && is_array($emails) && sizeof($emails)) {
            foreach ( $emails as $email ) {
                $voucher = self::generateVoucher($email, $voucherInfos ['type'], ($voucherInfos ['type'] == 'shipping' ? false : $voucherInfos ['amount']), ($voucherInfos ['type'] == 'amount' ? $voucherInfos ['amountCurrency'] : false), $voucherInfos ['minimumOrder'], $voucherInfos ['nbDayValidate'], '');
                if (! $voucher)
                    continue;
                $vouchers [$email] = $voucher;
            }
        }
        return $vouchers;
    }

    /**
     * Enregistrement de l'ID unique de la relance
     *
     * @param string $keysAccess
     * @return boolean
     */
    public static function generateKeysAccess($keysAccess) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        if ($keysAccess && is_array($keysAccess) && sizeof($keysAccess)) {
            foreach ( $keysAccess as $keyAccessInfo ) {
                $keyAccess = $keyAccessInfo ['key'];
                $id_customer = (int) $keyAccessInfo ['id_customer'];
                $id_cart = (int) $keyAccessInfo ['id_cart'];
                $id_order = $keyAccessInfo ['id_order'];
                $email = $keyAccessInfo ['email'];
                $voucher_number = $keyAccessInfo ['voucher_number'];
                $tablePrefix = Mage::getConfig()->getTablePrefix();
                $write = Mage::getSingleton('core/resource')->getConnection('core_write');
                $now = date('Y-m-d H:i:s');
                $write->query('INSERT IGNORE INTO `' . $tablePrefix . 'spmcartoorder` (`spm_key`' . ($id_customer ? ',`id_customer`' : '') . ($voucher_number ? ',`voucher_number`' : '') . ($id_cart ? ',`id_cart`' : '') . ($email ? ',`email`' : '') . ($id_order ? ',`id_order`' : '') . ',`date_add`,`date_upd`) values ("' . $keyAccess . '"' . ($id_customer ? ',' . $id_customer : '') . ($voucher_number ? ',"' . $voucher_number . '"' : '') . ($id_cart ? ',' . $id_cart : '') . ($email ? ',"' . $email . '"' : '') . ($id_order ? ',' . $id_order : '') . ',"' . $now . '","' . $now . '")');
            }
        }
        return true;
    }

    /**
     * Création code de réduction
     *
     * @param int $id_customer
     * @param int $type
     * @param string $amount
     * @param string $amountCurrency
     * @param string $minimumOrder
     * @param int $nbDayValidate
     * @param string $description
     * @return string boolean
     */
    public static function generateVoucher($id_customer, $type, $amount = false, $amountCurrency = false, $minimumOrder = false, $nbDayValidate, $description) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        $coupon_code = 'SPM-' . self::shortId();
        $date = date('Y-m-d H:i:s');
        $coupon = Mage::getModel('salesrule/rule');
        $coupon->setName($coupon_code)->setFromDate($date)->setToDate((date('Y-m-d 23:59:59', mktime(date("H"), date("i"), date("s"), date("m"), date("d") + $nbDayValidate, date("Y")))))->setUsesPerCustomer('1')->setIsActive('1')->setStopRulesProcessing('0')->setIsAdvanced('1')->setProductIds(NULL)->setSortOrder('0');
        if ($type == 'percent') {
            $coupon->setSimpleAction('by_percent')->setDiscountAmount($amount)->setDiscountQty(NULL)->setSimpleFreeShipping('0');
        }
        if ($type == 'amount') {
            $coupon->setSimpleAction('cart_fixed')->setDiscountAmount($amount)->setDiscountQty(NULL)->setSimpleFreeShipping('0');
        } elseif ($type == 'shipping') {
            $coupon->setSimpleFreeShipping(1);
        }
        $coupon->setDiscountStep('0')->setApplyToShipping('0')->setTimesUsed('0')->setIsRss('0')->setCouponType('2')->setUsesPerCoupon(1)->setCustomerGroupIds(self::getAllCustomerGroupsIds())->setWebsiteIds(array (
                '1'
        ))->setCouponCode($coupon_code);
        if ($minimumOrder) {
            $condition = Mage::getModel('salesrule/rule_condition_address')->setType('salesrule/rule_condition_address')->setAttribute('base_subtotal')->setOperator('>=')->setValue((int) $minimumOrder);
            $coupon->getConditions()->addCondition($condition);
        }
        if ($coupon->save())
            return $coupon_code;
        return false;
    }

    /**
     * Récupération des IDs de groupes de client
     *
     * @return array
     */
    public static function getAllCustomerGroupsIds() {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        $return = array ();
        $results = Mage::getResourceModel('customer/group_collection')->toOptionArray();
        if ($results) {
            foreach ( $results as $row ) {
                $return [] = $row ['value'];
            }
        }
        return $return;
    }

    /**
     * Récupération des de groupes de client
     *
     * @return array
     */
    public static function getCustomerGroups() {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        $return = array ();
        $results = Mage::getResourceModel('customer/group_collection')->toOptionArray();
        if ($results) {
            foreach ( $results as $row ) {
                $return [$row ['value']] = $row ['label'];
            }
        }
        return $return;
    }

    /**
     * Récupération des pays
     *
     * @return array
     */
    public static function getCountries() {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        $return = array ();
        $results = Mage::getResourceModel('directory/country_collection')->loadByStore()->toOptionArray();
        if ($results) {
            foreach ( $results as $row ) {
                if ($row ['value'])
                    $return [$row ['value']] = $row ['label'];
            }
        }
        return $return;
    }

    /**
     * Emulation de la langue de la boutique en fonction du store_id
     *
     * @param int $store_id
     * @return boolean void
     */
    public static function startLangEmulationByStoreId($store_id) {
        if ($store_id == Mage::app()->getStore()->getId())
            return false;
        $storeCode = Mage::app()->getStore($store_id)->getCode();
        self::$appEmulation = Mage::getSingleton('core/app_emulation');
        self::$initialEnvironmentInfo = self::$appEmulation->startEnvironmentEmulation($storeCode);
    }

    /**
     * Emulation de la langue de la boutique en fonction de l'ISO langue
     *
     * @param string $lang
     * @return boolean void
     */
    public static function startLangEmulationByIsoLang($lang) {
        if (self::$appEmulation !== false)
            return;
        $stores = Mage::app()->getStores();
        foreach ( $stores as $store ) {
            $store_id = $store->getId();
            $locale_store = Mage::getStoreConfig('general/locale/code', $store_id);
            if ($lang === substr($locale_store, 0, - 3)) {
                self::startLangEmulationByStoreId($store_id);
            }
        }
    }

    /**
     * Retour à l'environnement normal
     *
     * @param string $lang
     * @return boolean void
     */
    public static function stopLangEmulation() {
        if (self::$appEmulation === false)
            return;
        self::$appEmulation->stopEnvironmentEmulation(self::$initialEnvironmentInfo);
        self::$appEmulation = false;
        self::$initialEnvironmentInfo = false;
    }

    /**
     * Récupération des devises
     *
     * @return array
     */
    public static function getCurrencies() {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        $return = array ();
        $results = Mage::app()->getStore()->getAvailableCurrencyCodes(true);
        if ($results) {
            foreach ( $results as $value ) {
                if ($value)
                    $return [$value] = Mage::app()->getLocale()->getTranslation($value, 'nametocurrency');
            }
        }
        return $return;
    }

    /**
     * Récupération de données d'exemple
     *
     * @param string $lang
     * @return array
     */
    public static function getTestData($lang = false) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        if ($lang)
            self::startLangEmulationByIsoLang($lang);
        $return = array ();
        // Lien vers panier
        $return ['link_cart'] = str_replace(basename($_SERVER ['SCRIPT_NAME']) . '/', '', Mage::getUrl('checkout/cart', array (
                '_nosid' => true
        )));
        // Article au hasard
        $return ['articles'] = self::getProducts($lang, false, true);
        if ($lang)
            self::stopLangEmulation();
        return $return;
    }

    /**
     * Supression des bons de réduction ShopyMind périmés
     *
     * @return boolean
     */
    public static function deleteUnusedVouchers() {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        $date = date('Y-m-d H:i:s');
        $tablePrefix = Mage::getConfig()->getTablePrefix();
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $results = $read->fetchAll('SELECT `rule_id` AS `id`
                FROM `' . $tablePrefix . 'salesrule`
                WHERE `name` LIKE "SPM-%" AND `to_date` <= DATE_SUB("' . $date . '", INTERVAL 7 DAY)');
        if ($results && is_array($results) && sizeof($results)) {
            foreach ( $results as $row ) {
                $cartRule = Mage::getModel('salesrule/rule')->load($row ['id']);
                $cartRule->delete();
            }
        }
        return true;
    }

    /**
     * Récupération de produits
     *
     * @param string $lang
     * @param string $products
     * @param string $random
     * @param number $maxProducts
     * @return array
     */
    public static function getProducts($lang, $products = false, $random = false, $maxProducts = 3) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        if ($lang)
            self::startLangEmulationByIsoLang($lang);
        $return = array ();

        $collection = array ();
        if ($products) {
            $collection = Mage::getResourceModel('catalog/product_collection');
            Mage::getModel('catalog/layer')->prepareProductCollection($collection);
            $collection->addAttributeToFilter('entity_id', array (
                    'in' => $products
            ))->getSelect();
            $collection->addStoreFilter();
            $collection->setPage(1);
        } elseif ($random) {
            $collection = Mage::getResourceModel('catalog/product_collection');
            Mage::getModel('catalog/layer')->prepareProductCollection($collection);
            $collection->getSelect()->order('rand()');
            $collection->addStoreFilter();
            $collection->setPage(1, ($maxProducts ? $maxProducts : 3));
        }
        if ($collection && sizeof($collection)) {
            foreach ( $collection as $product ) {
                $image_url = str_replace(basename($_SERVER ['SCRIPT_NAME']) . '/', '', $product->getSmallImageUrl(200, 200));
                $product_url = str_replace(basename($_SERVER ['SCRIPT_NAME']) . '/', '', $product->getProductUrl(false));
                $return [] = array (
                        'description' => $product->getName(),
                        'price' => $product->getPrice(),
                        'image_url' => $image_url,
                        'product_url' => $product_url
                );
            }
        }
        if ($lang)
            self::stopLangEmulation();
        return $return;
    }

    /**
     * Récupération de la locale d'un client
     *
     * @param int|string $id_customer
     * @param int $store_id
     * @param string $country_code
     * @return string
     */
    public static function getUserLocale($id_customer, $store_id, $country_code = false) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        $locale_shop = Mage::getStoreConfig('general/locale/code', $store_id);
        if (! $country_code) {
            $customer = Mage::getModel('customer/customer')->load($id_customer);
            $defaultBilling = $customer->getDefaultBillingAddress();
            if ($defaultBilling)
                return substr($locale_shop, 0, 3) . $defaultBilling->getCountry();
        } else
            return substr($locale_shop, 0, 3) . $country_code;

        $locale_shop = explode('_', $locale_shop);
        $locale_shop = strtolower($locale_shop [0]) . '_00';

        return $locale_shop;
    }

    /**
     * Récupération du complément de requete de timezone
     *
     * @param array $timezones
     * @param string $country_alias
     * @param string $country_field
     * @param string $state_alias
     * @param string $state_field
     * @return boolean string
     */
    public static function generateTimezonesWhere($timezones, $country_alias = 'customer_default_billing_country', $country_field = 'value', $state_alias = 'customer_default_billing_state', $state_field = 'code') {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        if (is_array($timezones) and ! sizeof($timezones))
            return false;
        $where = array ();
        foreach ( $timezones as $zones ) {
            if (! is_array($zones))
                $where [] = '(`' . $country_alias . '`.`' . $country_field . '` IS NULL)';
            elseif (! isset($zones ['region']) || ! $zones ['region'])
                $where [] = '(`' . $country_alias . '`.`' . $country_field . '` = "' . $zones ['country'] . '")';
            else
                $where [] = '(`' . $country_alias . '`.`' . $country_field . '` = "' . $zones ['country'] . '" AND `' . $state_alias . '`.`' . $state_field . '` = "' . $zones ['region'] . '")';
        }
        if (sizeof($where))
            return '(' . implode(' OR ', $where) . ')';
        return false;
    }

    /**
     * Récupération date de dernière commande d'un client
     *
     * @param int|string $id_customer
     * @return string int
     */
    public static function getDateLastOrder($id_customer) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        $tablePrefix = Mage::getConfig()->getTablePrefix();
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        if (is_numeric($id_customer)) {
            $result = $read->fetchRow('SELECT MAX(`created_at`) AS `created_at`
			FROM `' . $tablePrefix . 'sales_flat_order`
			WHERE `customer_id` = ' . (int) $id_customer . ' AND `base_total_invoiced` IS NOT NULL');
        } else {
            $result = $read->fetchRow('SELECT MAX(`created_at`) AS `created_at`
			FROM `' . $tablePrefix . 'sales_flat_order`
			WHERE `customer_email` = "' . $id_customer . '" AND `base_total_invoiced` IS NOT NULL');
        }
        return isset($result ['created_at']) ? $result ['created_at'] : 0;
    }

    /**
     * Récupération du nombre de commande d'un client
     *
     * @param int|string $id_customer
     * @return int
     */
    public static function countCustomerOrder($id_customer) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        $tablePrefix = Mage::getConfig()->getTablePrefix();
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        if (is_numeric($id_customer)) {
            $result = $read->fetchRow('SELECT COUNT(`entity_id`) AS `nbOrder`
			FROM `' . $tablePrefix . 'sales_flat_order`
			WHERE `customer_id` = ' . (int) $id_customer . ' AND `base_total_invoiced` IS NOT NULL');
        } else {
            $result = $read->fetchRow('SELECT COUNT(`entity_id`) AS `nbOrder`
			FROM `' . $tablePrefix . 'sales_flat_order`
			WHERE `customer_email` = "' . $id_customer . '" AND `base_total_invoiced` IS NOT NULL');
        }
        return isset($result ['nbOrder']) ? $result ['nbOrder'] : 0;
    }

    /**
     * Récupération de la somme des commandes d'un client
     *
     * @param int|string $id_customer
     * @return int float
     */
    public static function sumCustomerOrder($id_customer) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        $mageVersion = Mage::getVersion();
        $tablePrefix = Mage::getConfig()->getTablePrefix();
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        if (is_numeric($id_customer)) {
            $result = $read->fetchRow('SELECT SUM((`base_total_invoiced`/base_to_order_rate)) AS `sumOrder`
			FROM `' . $tablePrefix . 'sales_flat_order`
			WHERE `customer_id` = ' . (int) $id_customer . ' AND `base_total_invoiced` IS NOT NULL');
        } else {
            $result = $read->fetchRow('SELECT SUM((`base_total_invoiced`/base_to_order_rate)) AS `sumOrder`
			FROM `' . $tablePrefix . 'sales_flat_order`
			WHERE `customer_email` = "' . $id_customer . '" AND `base_total_invoiced` IS NOT NULL');
        }
        return isset($result ['sumOrder']) ? $result ['sumOrder'] : 0;
    }

    /**
     * Récupération du code d'un attribut
     *
     * @param string $model
     * @param string $attribute_code
     * @return int
     */
    public static function getMagentoAttributeCode($model, $attribute_code) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        $eavAttribute = new Mage_Eav_Model_Mysql4_Entity_Attribute();
        return $eavAttribute->getIdByCode($model, $attribute_code);
    }

    /**
     * Récupération de l'id du client en session
     *
     * @return int boolean
     */
    public static function getSessionCustomerId() {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerData = Mage::getSingleton('customer/session')->getCustomer();
            return $customerData->getId();
        }
        return false;
    }

    /**
     * Récupération de l'email du client en session
     *
     * @return string boolean
     */
    public static function getSessionCustomerEmail() {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerData = Mage::getSingleton('customer/session')->getCustomer();
            return $customerData->getEmail();
        } else {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            return $quote->getCustomerEmail();
        }
        return false;
    }

    /**
     * Récupération de l'id du panier du client en session
     *
     * @return int boolean
     */
    public static function getSessionCartId() {
        $session = Mage::getSingleton('checkout/session');
        $cart_id = $session->getQuote()->getId();
        return $cart_id;
    }

    /**
     * Vérification d'un panier initié suite à une relance
     */
    public static function checkNewCart() {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        $cookie = Mage::app()->getCookie();
        $cart_id = self::getSessionCartId();
        $customer_id = self::getSessionCustomerId();
        $customer_email = self::getSessionCustomerEmail();
        $vouchersCart = (array) Mage::getSingleton('checkout/session')->getQuote()->getCouponCode();
        $key_checked = md5($cart_id . '-' . $customer_id . '-' . $customer_email . '-' . serialize($vouchersCart));
        if ((! isset($_GET ['spm_key']) || ! $_GET ['spm_key']) && (! $cookie->get('spmkeychecked') || $key_checked !== $cookie->get('spmkeychecked')) && ! $cookie->get('spmcartoorder') && ! $cookie->get('spmcartoorder_persistent') && $cart_id) {
            $tablePrefix = Mage::getConfig()->getTablePrefix();
            $read = Mage::getSingleton('core/resource')->getConnection('core_read');
            $spm_key = $read->fetchRow('SELECT `spm_key` FROM `' . $tablePrefix . 'spmcartoorder` WHERE (`id_cart` = ' . (int) $cart_id . ') AND `is_converted` = 0 AND `date_upd` >= DATE_SUB("' . date('Y-m-d H:i:s') . '", INTERVAL 1 MONTH) ORDER BY `date_add` DESC');
            if (! $spm_key && $customer_id)
                $spm_key = $read->fetchRow('SELECT `spm_key` FROM `' . $tablePrefix . 'spmcartoorder` WHERE (`id_customer` = ' . (int) $customer_id . ') AND `is_converted` = 0 AND `date_upd` >= DATE_SUB("' . date('Y-m-d H:i:s') . '", INTERVAL 1 MONTH) ORDER BY `date_add` DESC');
            if (! $spm_key && $customer_email !== null && $customer_email !== false && $customer_email)
                $spm_key = $read->fetchRow('SELECT `spm_key` FROM `' . $tablePrefix . 'spmcartoorder` WHERE (`email` = "' . $customer_email . '") AND `is_converted` = 0 AND `date_upd` >= DATE_SUB("' . date('Y-m-d H:i:s') . '", INTERVAL 1 MONTH) ORDER BY `date_add` DESC');
            if (! $spm_key) {
                if ($vouchersCart && sizeof($vouchersCart)) {
                    foreach ( $vouchersCart as $voucher ) {
                        if (! $spm_key && $voucher)
                            $spm_key = $read->fetchRow('SELECT `spm_key` FROM `' . $tablePrefix . 'spmcartoorder` WHERE (`voucher_number` = "' . addslashes($voucher) . '") AND `is_converted` = 0 AND `date_upd` >= DATE_SUB("' . date('Y-m-d H:i:s') . '", INTERVAL 1 MONTH) ORDER BY `date_add` DESC');
                        else
                            break;
                    }
                }
            }
            if ($spm_key && isset($spm_key ['spm_key']) && $spm_key ['spm_key']) {
                $_GET ['spm_key'] = $spm_key ['spm_key'];
                $cookie->delete('spmkeychecked');
            } else {
                $cookie->set('spmkeychecked', $key_checked, 0);
            }
        }
        if ((isset($_GET ['spm_key']) && $_GET ['spm_key']) || $cookie->get('spmcartoorder') || $cookie->get('spmcartoorder_persistent')) {
            $quote = Mage::getSingleton('checkout/cart')->getQuote();
            if (! $cart_id || (! $customer_id && ! $customer_email) || ! $quote->getGrandTotal()) {
                if (isset($_GET ['spm_key']) && $_GET ['spm_key']) {
                    $cookie->set('spmcartoorder_persistent', $_GET ['spm_key'], 657000);
                    $cookie->set('spmcartoorder', $_GET ['spm_key'], 0);
                }
            } else {

                $tablePrefix = Mage::getConfig()->getTablePrefix();

                if (isset($_GET ['spm_key']) && $_GET ['spm_key'])
                    $spm_key = $_GET ['spm_key'];
                elseif ($cookie->get('spmcartoorder') != '')
                    $spm_key = $cookie->get('spmcartoorder');
                elseif ($cookie->get('spmcartoorder_persistent') != '') {
                    $spm_key = $cookie->get('spmcartoorder_persistent');
                    $read = Mage::getSingleton('core/resource')->getConnection('core_read');
                    $test = $read->fetchRow('SELECT `spm_key` FROM `' . $tablePrefix . 'spmcartoorder` WHERE `spm_key` = "' . addslashes($spm_key) . '" AND `is_converted` = 1');
                    if ($test && isset($test ['spm_key']))
                        return;
                }

                // Cart already registred
                if ($cookie->get('spmlastcartregister') && $cookie->get('spmlastcartregister') == $spm_key . $cart_id)
                    return;
                $now = date('Y-m-d H:i:s');
                $write = Mage::getSingleton('core/resource')->getConnection('core_write');
                $write->query('INSERT INTO `' . $tablePrefix . 'spmcartoorder` (`id_cart`,`id_customer`,`email`,`spm_key`,`date_upd`) values (' . (int) $cart_id . ',' . (int) $customer_id . ',"' . addslashes($customer_email) . '","' . addslashes($spm_key) . '","' . $now . '") ON DUPLICATE KEY UPDATE id_cart = ' . (int) $cart_id . ',id_customer = ' . (int) $customer_id . ', email="' . addslashes($customer_email) . '", spm_key="' . addslashes($spm_key) . '", date_upd="' . $now . '"');
                include_once (Mage::getBaseDir('base') . '/lib/ShopymindClient/Bin/Notify.php');
                if (ShopymindClient_Bin_Notify::newCart(array (
                        'idRemindersSend' => $spm_key,
                        'idCart' => $cart_id,
                        'totalCart' => $quote->getGrandTotal(),
                        'currencyCart' => $quote->getQuoteCurrencyCode(),
                        'taxRateCart' => $quote->getStoreToQuoteRate()
                ))) {
                    $cookie->set('spmlastcartregister', $spm_key . $cart_id, 0); /* keep cookie to avoid session problem */
                    $cookie->delete('spmcartoorder');
                    $cookie->delete('spmcartoorder_persistent');
                }
            }
        }
    }

    /**
     * Remontée des ventes vers ShopyMind
     *
     * @param array $order
     */
    public static function checkNewOrder($order) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        $orderData = $order->getData();
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tablePrefix = Mage::getConfig()->getTablePrefix();
        $spm_key = $read->fetchRow('SELECT `spm_key` FROM `' . $tablePrefix . 'spmcartoorder` WHERE (`id_cart` = ' . (int) $orderData ['quote_id'] . ') AND `date_upd` >= DATE_SUB("' . date('Y-m-d H:i:s') . '", INTERVAL 1 MONTH) AND DATE_FORMAT(`date_add`,"%Y-%m-%d %H:%i:%s") < DATE_FORMAT("' . $orderData ['created_at'] . '","%Y-%m-%d %H:%i:%s") AND `is_converted` = 0 ORDER BY `date_add` DESC');

        $voucherUsed = array ();
        $vouchersOrder = $order->getCouponCode();
        if ($vouchersOrder)
            $voucherUsed [] = $vouchersOrder;

        if (sizeof($voucherUsed) && ! $spm_key) {
            foreach ( $voucherUsed as $voucher ) {
                if (! $spm_key && $voucher)
                    $spm_key = $read->fetchRow('SELECT `spm_key` FROM `' . $tablePrefix . 'spmcartoorder` WHERE (`voucher_number` = "' . addslashes($voucher) . '") AND `date_upd` >= DATE_SUB("' . date('Y-m-d H:i:s') . '", INTERVAL 1 MONTH) AND DATE_FORMAT(`date_add`,"%Y-%m-%d %H:%i:%s") < DATE_FORMAT("' . $orderData ['created_at'] . '","%Y-%m-%d %H:%i:%s") AND `is_converted` = 0 ORDER BY `date_add` DESC');
                else
                    break;
            }
        }

        if (! $spm_key)
            $spm_key = $read->fetchRow('SELECT `spm_key` FROM `' . $tablePrefix . 'spmcartoorder` WHERE (`id_order` = ' . (int) $orderData ['entity_id'] . ') AND `date_upd` >= DATE_SUB("' . date('Y-m-d H:i:s') . '", INTERVAL 1 MONTH) AND DATE_FORMAT(`date_add`,"%Y-%m-%d %H:%i:%s") < DATE_FORMAT("' . $orderData ['created_at'] . '","%Y-%m-%d %H:%i:%s") AND `is_converted` = 0 ORDER BY `date_add` DESC');
        if (! $spm_key && isset($orderData ['customer_id']) && $orderData ['customer_id'])
            $spm_key = $read->fetchRow('SELECT `spm_key` FROM `' . $tablePrefix . 'spmcartoorder` WHERE (`id_customer` = ' . (int) $orderData ['customer_id'] . ') AND `date_upd` >= DATE_SUB("' . date('Y-m-d H:i:s') . '", INTERVAL 1 MONTH) AND DATE_FORMAT(`date_add`,"%Y-%m-%d %H:%i:%s") < DATE_FORMAT("' . $orderData ['created_at'] . '","%Y-%m-%d %H:%i:%s") AND `is_converted` = 0 ORDER BY `date_add` DESC');
        if (! $spm_key && isset($orderData ['customer_email']) && $orderData ['customer_email'])
            $spm_key = $read->fetchRow('SELECT `spm_key` FROM `' . $tablePrefix . 'spmcartoorder` WHERE (`email` = "' . $orderData ['customer_email'] . '") AND `date_upd` >= DATE_SUB("' . date('Y-m-d H:i:s') . '", INTERVAL 1 MONTH) AND DATE_FORMAT(`date_add`,"%Y-%m-%d %H:%i:%s") < DATE_FORMAT("' . $orderData ['created_at'] . '","%Y-%m-%d %H:%i:%s") AND `is_converted` = 0 ORDER BY `date_add` DESC');

        if ($spm_key && isset($spm_key ['spm_key']) && $spm_key ['spm_key']) {

            self::sendOrderToSPM($order, $orderData, $spm_key ['spm_key'], $voucherUsed);
        }
    }

    /**
     * Envoi des ventes générées suite à une relance à ShopyMind
     *
     * @param object $order
     * @param array $orderData
     * @param string $spm_key
     * @param array $voucherUsed
     */
    public static function sendOrderToSPM($order, $orderData, $spm_key, $voucherUsed) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());

        include_once (Mage::getBaseDir('base') . '/lib/ShopymindClient/Bin/Notify.php');
        $quote = Mage::getSingleton('sales/quote')->load((int) $orderData ['quote_id']);
        $params = array (
                'idRemindersSend' => $spm_key,
                'idCart' => $orderData ['quote_id'],
                'dateCart' => ($quote->getUpdatedAt() !== null && $quote->getUpdatedAt() !== '' ? $quote->getUpdatedAt() : $orderData ['created_at']),
                'idOrder' => $orderData ['increment_id'],
                'amount' => $orderData ['base_total_paid'],
                'taxRate' => $orderData ['base_to_order_rate'],
                'currency' => $orderData ['order_currency_code'],
                'dateOrder' => $orderData ['created_at'],
                'voucherUsed' => $voucherUsed,
                'customer' => self::getUser(($orderData ['customer_id'] ? $orderData ['customer_id'] : $orderData ['customer_email']))
        );
        $spm_key = ShopymindClient_Bin_Notify::newOrder($params);
        if ($spm_key && isset($spm_key ['idRemindersSend']) && $spm_key ['idRemindersSend']) {
            $tablePrefix = Mage::getConfig()->getTablePrefix();
            $write = Mage::getSingleton('core/resource')->getConnection('core_write');
            $write->query('UPDATE `' . $tablePrefix . 'spmcartoorder` SET `is_converted` = 1 WHERE `spm_key` = "' . $spm_key ['idRemindersSend'] . '"');
        }
    }
}