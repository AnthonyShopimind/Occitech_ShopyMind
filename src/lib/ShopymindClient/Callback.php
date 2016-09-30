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
    const SEARCH_MIN_LENGTH = 3;

    /**
     * @var null|int Current timestamp (to allow simulating time changes from tests)
     */
    public static $now = null;

    /**
     * Allow to fetch user information from either an id or email address(es)
     *
     * @param mixed|array $idOrEmails If single and numeric, then it's an id otherwise a list of email addresses
     * @return array
     */
    public static function getUser($idOrEmails, $fromQuotes = false) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());

        $GetUserAction = new SPM_ShopyMind_Action_GetUser($idOrEmails, $fromQuotes);
        return $GetUserAction->process();
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
                $return [$row['status']] = $row['label'];
            }
        }
        return $return;
    }

    /**
     * Get store customers' timezones
     *
     * Example of expected result:
     * array(
     *     array(
     *         'country_code' => 'US',
     *         'region_code' => 'GA',
     *     )
     * )
     *
     * @param int $id_shop Magento store id
     * @param string|false $lastUpdate Only get timezones for customers create since $lastUpdate
     * @return array
     */
    public static function getTimezones($id_shop, $lastUpdate) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());

        $return = array ();
        $tablePrefix = Mage::getConfig()->getTablePrefix();
        $resource = Mage::getSingleton('core/resource');

        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId($id_shop);

        $readConnection = $resource->getConnection('core_read');
        $query = 'SELECT a.`value` as `country_code`, c.`code` as `region_code`
         FROM `' . $tablePrefix . 'customer_entity` AS d
        LEFT JOIN `' . $tablePrefix . 'customer_address_entity_varchar` a ON(d.`entity_id` = a.`entity_id`)
        LEFT JOIN `' . $tablePrefix . 'customer_address_entity_int` b ON(a.`entity_id` = b.`entity_id`)
        LEFT JOIN `' . $tablePrefix . 'directory_country_region` c ON(c.`region_id` = b.`value`)
        WHERE (b.`attribute_id` = ' . self::getMagentoAttributeCode('customer_address', 'region_id') . ' OR b.`attribute_id` IS NULL) AND ( a.`attribute_id` = ' . self::getMagentoAttributeCode('customer_address', 'country_id') . ')
        ' . ($lastUpdate ? ' AND d.`updated_at` >= "' . $lastUpdate . '"' : '') . '
        AND d.store_id IN ("' . implode('","', $scope->storeIds()) . '")
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
     * Get customers having their bithday at the given date
     *
     * Example of expected result:
     * array(
     *     array(
     *         'customer' => array(
     *             'id_customer' => '',
     *             'last_name' => '',
     *             'first_name' => '',
     *             'email_address' => '',
     *             'gender' => '', // 1 = man, 2 = woman, 0 = undefined ; might not be present
     *             'locale' => '', // might not be present
     *             'voucher_number' => '', // might not be present
     *             // many other magento customer
     *         )
     *     )
     * )
     *
     * @param int $id_shop Magento store id
     * @param string $dateReference Birthday date ; format: Y-m-d H:i:s
     * @param array $timezones Timezones to use
     * @param int $nbDays Minus delta for date reference
     * @param bool $justCount Return the count instead of a list
     *
     * @return array|int
     */
    public static function getBirthdayClients($id_shop, $dateReference, $timezones, $nbDays = 0, $justCount = false) {
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

        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId($id_shop);

        $readConnection = $resource->getConnection('core_read');
        $query = 'SELECT `customer_primary_table`.`entity_id`
         FROM `' . $tablePrefix . 'customer_entity` AS `customer_primary_table`
         INNER JOIN `' . $tablePrefix . 'customer_entity_datetime` AS `customer_birth_table` ON(`customer_birth_table`.`entity_id` = `customer_primary_table`.`entity_id` AND `customer_birth_table`.`attribute_id` = ' . self::getMagentoAttributeCode('customer', 'dob') . ')
         LEFT JOIN `' . $tablePrefix . 'customer_entity_int` AS `customer_default_billing_jt` ON (`customer_default_billing_jt`.`entity_id` = `customer_primary_table`.`entity_id`) AND (`customer_default_billing_jt`.`attribute_id` = ' . self::getMagentoAttributeCode('customer', 'default_shipping') . ')
         LEFT JOIN `' . $tablePrefix . 'customer_address_entity_varchar` AS `customer_default_billing_country` ON (`customer_default_billing_jt`.`value` = `customer_default_billing_country`.`entity_id`) AND (`customer_default_billing_country`.`attribute_id` = ' . self::getMagentoAttributeCode('customer_address', 'country_id') . ')
         LEFT JOIN `' . $tablePrefix . 'customer_address_entity_int` AS `customer_default_billing_state_jt` ON (`customer_default_billing_country`.`entity_id` = `customer_default_billing_state_jt`.`entity_id`) AND (`customer_default_billing_state_jt`.`attribute_id` = ' . self::getMagentoAttributeCode('customer_address', 'region_id') . ' OR `customer_default_billing_state_jt`.`attribute_id` IS NULL)
         LEFT JOIN `' . $tablePrefix . 'directory_country_region` AS `customer_default_billing_state` ON(`customer_default_billing_state`.`region_id` = `customer_default_billing_state_jt`.`value`)
        WHERE  DATE_FORMAT(`customer_birth_table`.`value`,"%m-%d") = "' . $birthDate . '" AND ' . $timezonesWhere . ' AND `customer_primary_table`.`is_active` = 1
        AND `customer_primary_table`.`store_id` IN ("' . implode('","', $scope->storeIds()) . '")
        GROUP BY `customer_primary_table`.`entity_id`';
        $results = $readConnection->fetchAll($query);

        if ($results && is_array($results) && sizeof($results)) {
            foreach ( $results as $row ) {
                if (! $row['entity_id'])
                    continue;
                $return [] = array (
                        'customer' => self::getUser($row['entity_id'])
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
                            'customer' => self::getUser($row['customer_email'])
                    );
                }
            }
        }

        return ($justCount ? array (
                'count' => sizeof($return)
        ) : $return);
    }

    /**
     * Retrieve customer which have their signup anniversary corresponding to $dateReference
     *
     * @param int $storeId
     * @param string $dateReference usually today's date. The date used to determine the signup birthday (only the day / month are useful)
     * @param array $timezones
     * @param bool $justCount
     *
     * @return array $customers
     */
    public static function getBirthdayClientsSignUp($storeId, $dateReference, $timezones, $justCount = false)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__)) {
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        }

        $customerCollection = Mage::getModel('customer/customer')
            ->getCollection()
            ->addFieldToFilter('created_at', array('like' => date('%-m-d%', strtotime($dateReference))))
            ->addFieldToFilter('created_at', array('nlike' => date('Y-m-d%', strtotime($dateReference))))
            ->addAttributeToSelect('entity_id');

        SPM_ShopyMind_Model_Scope::fromShopymindId($storeId)->restrictCollection($customerCollection);

        $attributeAliasMethod = new ReflectionMethod($customerCollection, '_getAttributeTableAlias');
        $attributeAliasMethod->setAccessible(true);
        $country_id_alias = $attributeAliasMethod->invoke($customerCollection, 'customer_country_id');
        $timezonesWhere = self::generateTimezonesWhere($timezones, $country_id_alias, 'value', 'directory_country_region');
        if (!empty($timezonesWhere)) {
            $customerCollection
                ->joinAttribute('customer_country_id', 'customer_address/country_id', 'default_billing', null, 'left')
                ->joinAttribute('customer_region_id', 'customer_address/region_id', 'default_billing', null, 'left')
                ->joinTable('directory/country_region', 'region_id=customer_region_id', array('code'), null, 'left');
            $customerCollection->getSelect()->where($timezonesWhere);
        }

        return self::returnCollectionDataOrCount($customerCollection, $justCount);
    }

    /**
     * Allow to get carts abandoned since a given period in seconds
     *
     * @param string $id_shop Shopymind ID
     * @param int $nbSeconds Nombre de secondes d'inactivité du panier
     * @param int $nbSecondsMaxInterval Nombre de secondes maximum d'inactivité du panier
     * @param bool $justCount
     * @return array Either the cart details, or an array with the counter: array('count' => xx)
     */
    public static function getDroppedOutCart($id_shop, $nbSeconds, $nbSecondsMaxInterval, $justCount = false) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__)) {
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        }

        $return = array();
        $now = date('Y-m-d H:i:s', empty(static::$now) ? time() : strtotime('now', static::$now)); // useful to allow simulating time for testing purposes
        $tablePrefix = Mage::getConfig()->getTablePrefix();
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId($id_shop);

        $query = '
          SELECT `quote_table`.*
          FROM `' . $tablePrefix . 'sales_flat_quote` AS `quote_table`
          LEFT JOIN `' . $tablePrefix . 'sales_flat_order` AS `order_table` ON (
            (`order_table`.`customer_email` = `quote_table`.`customer_email`
            OR `order_table`.`customer_id` = `quote_table`.`customer_id`)
            AND `order_table`.`created_at` >= DATE_SUB("' . $now . '",  INTERVAL 7 DAY)
          )
          WHERE
            (`quote_table`.`reserved_order_id` = "" OR `quote_table`.`reserved_order_id` IS NULL)
            AND (DATE_FORMAT(`quote_table`.`updated_at`,"%Y-%m-%d %H:%i:%s") >= DATE_SUB("' . $now . '", INTERVAL ' . ($nbSeconds + $nbSecondsMaxInterval) . ' SECOND))
            AND (DATE_FORMAT(`quote_table`.`updated_at`,"%Y-%m-%d %H:%i:%s") <= DATE_SUB("' . $now . '", INTERVAL ' . ($nbSeconds) . ' SECOND))
            AND (`quote_table`.`items_count` > 0)
            AND (`quote_table`.`customer_id` IS NOT NULL OR `quote_table`.`customer_email` IS NOT NULL)
            AND (`order_table`.`entity_id` IS NULL)
            AND `quote_table`.`store_id` IN ("' . implode('","', $scope->storeIds()) . '")
          GROUP BY `quote_table`.`customer_email`
        ';

        $results = $readConnection->fetchAll($query);
		$countIds = count($scope->storeIds());
		
        if (!empty($results) && is_array($results)) {
            foreach($results as $row) {
                $cartProducts = self::productsOfCart($scope, ($countIds > 1) ? $row['store_id'] : null, $row['entity_id']);
                if (!empty($cartProducts)) {
                    $return[] = array(
                        'sum_cart' => ($row['base_grand_total'] / $row['store_to_base_rate']),
                        'currency' => $row['base_currency_code'],
                        'tax_rate' => $row['store_to_base_rate'],
                        'id_cart' => $row['entity_id'],
                        'date_cart' => $row['created_at'],
                        'link_cart' => Mage::helper('shopymind')->getUrl('checkout/cart', array(
							'_current' => false,
							'_use_rewrite' => true,
							'_secure' => true,
							'_store' => $row['store_id'],
							'_store_to_url' => false,
							'_nosid' => true
						)),
                        'products' => $cartProducts,
                        'customer' => self::getUser(($row['customer_id'] ? $row['customer_id'] : $row['customer_email']), true)
                    );
                }
            }
        }
        return ($justCount ? array('count' => count($return)) : $return);
    }

    private static function productsOfCart(SPM_ShopyMind_Model_Scope $scope, $storeId = null, $cartId)
    {
        $helper = Mage::helper('shopymind');
        $emulatedEnvironment = $helper->startEmulatingScope($scope, $storeId);
		
        $currentCart = Mage::getModel('sales/quote')->load($cartId);

        $resultProducts = $currentCart->getAllVisibleItems();
        if (empty($resultProducts) || !is_array($resultProducts)) {
            return array();
        }

        $DataTransformer = new SPM_ShopyMind_DataMapper_DataTransformer_QuoteItemToProduct();
        $ProductMapper = new SPM_ShopyMind_DataMapper_Product();
        $formatter = new SPM_ShopyMind_DataMapper_Pipeline(array(
            array($DataTransformer, 'transform'),
            array($ProductMapper, 'formatProductWithCombination'),
            SPM_ShopyMind_DataMapper_Scope::makeScopeEnricher($scope),
        ));
        $data = $formatter->format($resultProducts);
        $helper->stopEmulation($emulatedEnvironment);

        return $data;
    }

    /**
     * Get the most spender customers for a defined date and having no order after the said date
     *
     * Example of expected result:
     * array(
     *     array(
     *         'customer' => array(
     *             'id_customer' => '',
     *             'last_name' => '',
     *             'first_name' => '',
     *             'email_address' => '',
     *             'gender' => '', // 1 = man, 2 = woman, 0 = undefined ; might not be present
     *             'locale' => '', // might not be present
     *             'voucher_number' => '', // might not be present
     *             // many other magento customer
     *         )
     *     )
     * )
     *
     * @param int $id_shop Magento store id
     * @param string $dateReference At which date ; format Y-m-d H:i:s
     * @param array $timezones Timezones to use
     * @param float $amount Minimum amount for fetched carts
     * @param float $amountMax Maximum amount for fetched carts
     * @param int $duration Duration in days from $dateReference
     * @param int $nbDaysLastOrder Duration in days since $dateReference that the customer ordered for the last time
     * @param bool $justCount Return the count instead of a list
     *
     * @return array|int
     */
    public static function getGoodClientsByAmount($id_shop, $dateReference, $timezones, $amount, $amountMax, $duration, $nbDaysLastOrder, $justCount = false) {
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
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId($id_shop);
        if (! $timezonesWhere)
            return false;
        $query = 'SELECT `order_last`.`entity_id`, `order_primary`.`customer_id`, `order_primary`.`customer_email`,
            SUM((`order_primary`.`base_total_invoiced`*`order_primary`.`base_to_order_rate`)) AS Total
            FROM `' . $tablePrefix . 'sales_flat_order` AS `order_primary`
            LEFT JOIN `' . $tablePrefix . 'sales_flat_order` AS `order_last` ON (((`order_last`.`customer_id` IS NOT NULL AND `order_last`.`customer_id` = `order_primary`.`customer_id`)) AND DATE_FORMAT(`order_last`.`created_at`,"%Y-%m-%d %H:%i:%s") >= DATE_FORMAT(DATE_SUB("' . $dateReference . '", INTERVAL ' . ((int) $nbDaysLastOrder - 1) . ' DAY),"%Y-%m-%d %H:%i:%s"))
            LEFT JOIN `' . $tablePrefix . 'sales_flat_order` AS `order_last2` ON (((`order_last2`.`customer_id` IS NOT NULL AND `order_last2`.`customer_id` = `order_primary`.`customer_id`)) AND DATE_FORMAT(`order_last2`.`created_at`,"%Y-%m-%d") = DATE_FORMAT(DATE_SUB("' . $dateReference . '", INTERVAL ' . (int) $nbDaysLastOrder . ' DAY),"%Y-%m-%d"))
            LEFT JOIN `' . $tablePrefix . 'sales_flat_order_address` AS `order_address` ON(`order_address`.`parent_id` = `order_primary`.`entity_id`) AND (`order_address`.`address_type` = "billing")
            LEFT JOIN `' . $tablePrefix . 'directory_country_region` AS `customer_default_billing_state` ON(`customer_default_billing_state`.`region_id` = `order_address`.`region_id`)
            WHERE DATE_FORMAT(DATE_SUB("' . $dateReference . '",INTERVAL ' . (int) $duration . ' DAY),"%Y-%m-%d %H:%i:%s") <= DATE_FORMAT(`order_primary`.`created_at`,"%Y-%m-%d %H:%i:%s")
            AND ' . $timezonesWhere . '
            AND `order_primary`.`base_total_invoiced` IS NOT NULL
            AND `order_last2`.`base_total_invoiced` IS NOT NULL
            AND `order_last`.`entity_id` IS NULL
            AND `order_last2`.`entity_id` IS NOT NULL
            AND `order_primary`.`store_id` IN ("' . implode('","', $scope->storeIds()) . '")
            GROUP BY `order_primary`.`customer_email`
    		HAVING `Total` >= ' . (float) $amount . '
    		' . ($amountMax ? ' AND `Total` <= ' . (float) $amountMax : '');
        $results = $readConnection->fetchAll($query);
        if ($results && is_array($results) && sizeof($results)) {
            foreach ( $results as $row ) {
                $return [] = array (
                        'customer' => self::getUser(($row['customer_id'] ? $row['customer_id'] : $row['customer_email']))
                );
            }
        }
        return ($justCount ? array (
                'count' => sizeof($return)
        ) : $return);
    }

    /**
     * Get customers having the largest amount of orders for a given period in time
     *
     * Example of expected result:
     * array(
     *     array(
     *         'customer' => array(
     *             'id_customer' => '',
     *             'last_name' => '',
     *             'first_name' => '',
     *             'email_address' => '',
     *             'gender' => '', // 1 = man, 2 = woman, 0 = undefined ; might not be present
     *             'locale' => '', // might not be present
     *             'voucher_number' => '', // might not be present
     *             // many other magento customer
     *         )
     *     )
     * )
     *
     * @param int $id_shop Magento store id
     * @param string $dateReference At which date ; format Y-m-d H:i:s
     * @param array $timezones Timezones to use
     * @param float $nbOrder Minimum orders amount
     * @param float $nbOrderMax Maximum orders amount
     * @param int $duration Duration in days from $dateReference
     * @param int $nbDaysLastOrder Duration in days since $dateReference that the customer ordered for the last time
     * @param bool $justCount Return the count instead of a list
     *
     * @return array|int
     */
    public static function getGoodClientsByNumberOrders($id_shop, $dateReference, $timezones, $nbOrder, $nbOrderMax, $duration, $nbDaysLastOrder, $justCount = false) {
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
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId($id_shop);
        if (! $timezonesWhere)
            return false;
        $query = 'SELECT `order_last`.`entity_id`, `order_primary`.`customer_id`, `order_primary`.`customer_email`,
            COUNT(`order_primary`.`entity_id`) AS Total
            FROM `' . $tablePrefix . 'sales_flat_order` AS `order_primary`
            LEFT JOIN `' . $tablePrefix . 'sales_flat_order` AS `order_last` ON (((`order_last`.`customer_id` IS NOT NULL AND `order_last`.`customer_id` = `order_primary`.`customer_id`)) AND DATE_FORMAT(`order_last`.`created_at`,"%Y-%m-%d %H:%i:%s") >= DATE_FORMAT(DATE_SUB("' . $dateReference . '", INTERVAL ' . ((int) $nbDaysLastOrder - 1) . ' DAY),"%Y-%m-%d %H:%i:%s"))
            LEFT JOIN `' . $tablePrefix . 'sales_flat_order` AS `order_last2` ON (((`order_last2`.`customer_id` IS NOT NULL AND `order_last2`.`customer_id` = `order_primary`.`customer_id`)) AND DATE_FORMAT(`order_last2`.`created_at`,"%Y-%m-%d") = DATE_FORMAT(DATE_SUB("' . $dateReference . '", INTERVAL ' . (int) $nbDaysLastOrder . ' DAY),"%Y-%m-%d"))
            LEFT JOIN `' . $tablePrefix . 'sales_flat_order_address` AS `order_address` ON(`order_address`.`parent_id` = `order_primary`.`entity_id`) AND (`order_address`.`address_type` = "billing")
            LEFT JOIN `' . $tablePrefix . 'directory_country_region` AS `customer_default_billing_state` ON(`customer_default_billing_state`.`region_id` = `order_address`.`region_id`)
            WHERE DATE_FORMAT(DATE_SUB("' . $dateReference . '",INTERVAL ' . (int) $duration . ' DAY),"%Y-%m-%d %H:%i:%s") <= DATE_FORMAT(`order_primary`.`created_at`,"%Y-%m-%d %H:%i:%s")
            AND ' . $timezonesWhere . '
            AND `order_primary`.`base_total_invoiced` IS NOT NULL
            AND `order_last2`.`base_total_invoiced` IS NOT NULL
            AND `order_last`.`entity_id` IS NULL
            AND `order_last2`.`entity_id` IS NOT NULL
            AND `order_primary`.`store_id` IN ("' . implode('","', $scope->storeIds()) . '")
            GROUP BY `order_primary`.`customer_email`
    		HAVING `Total` >= ' . (int) $nbOrder . '
    		' . ($nbOrderMax ? ' AND `Total` <= ' . (int) $nbOrderMax : '');
        $results = $readConnection->fetchAll($query);

        if ($results && is_array($results) && sizeof($results)) {
            foreach ( $results as $row ) {
                $return [] = array (
                        'customer' => self::getUser(($row['customer_id'] ? $row['customer_id'] : $row['customer_email']))
                );
            }
        }
        return ($justCount ? array (
                'count' => sizeof($return)
        ) : $return);
    }

    /**
     * Get customers who never ordered
     *
     * Example of expected result:
     * array(
     *     array(
     *         'customer' => array(
     *             'id_customer' => '',
     *             'last_name' => '',
     *             'first_name' => '',
     *             'email_address' => '',
     *             'gender' => '', // 1 = man, 2 = woman, 0 = undefined ; might not be present
     *             'locale' => '', // might not be present
     *             'voucher_number' => '', // might not be present
     *             // many other magento customer
     *         )
     *     )
     * )
     *
     * @param int $id_shop Magento store id
     * @param string $dateReference Date from which we search customers that have not ordered
     * @param array $timezones Timezones to use
     * @param int $nbDays Duration in days from $dateReference
     * @param bool $relaunchOlder Not used in Magento client
     * @param bool $justCount Return the count instead of a list
     *
     * @return array|int
     */
    public static function getMissingClients($id_shop, $dateReference, $timezones, $nbDays = 0, $relaunchOlder = false, $justCount = false) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        $return = array ();

        $timezonesWhere = self::generateTimezonesWhere($timezones);
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId($id_shop);
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
        AND `customer_primary_table`.`store_id` IN ("' . implode('","', $scope->storeIds()) . '")
        GROUP BY `customer_order`.`customer_id`';
        $results = $readConnection->fetchAll($query);

        if ($results && is_array($results) && sizeof($results)) {
            foreach ( $results as $row ) {
                if (! $row['entity_id'])
                    continue;
                $return [] = array (
                        'customer' => self::getUser($row['entity_id'])
                );
            }
        }

        return ($justCount ? array (
                'count' => sizeof($return)
        ) : $return);
    }

    /**
     * Get orders having a given status since $dateReference
     *
     * Example of expected result:
     * array(
     *     array(
     *         'articles' => array(
     *             // array of order products data
     *         ),
     *         'customer' => array(
     *             // customer data
     *         ),
     *         'currency' => '', //currency code
     *         'total_amount' => '',
     *         'id_order' => '',
     *     )
     * )
     *
     * @param int $id_shop Magento store id
     * @param string $dateReference Date the orders was set to the given status
     * @param array $timezones Timezones to use
     * @param int $nbDays
     * @param mixed $idStatus Magento status code
     * @param bool $justCount Return the count instead of a list
     *
     * @return array|int
     */
    public static function getOrdersByStatus($id_shop, $dateReference, $timezones, $nbDays, $idStatus, $justCount = false) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__)) {
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        }
        $tablePrefix = Mage::getConfig()->getTablePrefix();
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId($id_shop);

        $timezonesWhere = self::generateTimezonesWhere($timezones, 'order_address', 'country_id');
        if (!$timezonesWhere) {
            return false;
        }
        $query = '
            SELECT
                `order_primary`.`store_id`,
                `order_primary`.`entity_id`,
                `order_primary`.`increment_id`,
                `order_primary`.`order_currency_code`,
                `order_primary`.`base_grand_total`,
                `order_primary`.`customer_id`,
                `order_primary`.`created_at`,
                `order_primary`.`customer_email`,
                `order_primary`.`quote_id`
            FROM `' . $tablePrefix . 'sales_flat_order` AS `order_primary`
            LEFT JOIN `' . $tablePrefix . 'sales_flat_order` AS `order_last` ON (
                (
                    (
                        `order_last`.`customer_id` IS NOT NULL
                        AND `order_last`.`customer_id` = `order_primary`.`customer_id`
                    )
                    OR (
                        (`order_last`.`customer_id` IS NULL OR `order_last`.`customer_id` = 0)
                        AND `order_last`.`customer_email` = `order_primary`.`customer_email`
                    )
                )
                AND `order_last`.`created_at` > `order_primary`.`created_at`
            )
            LEFT JOIN `' . $tablePrefix . 'sales_flat_order_status_history` AS `order_status` ON (
                `order_status`.`status` = `order_primary`.`status`
                AND `order_status`.`parent_id` = `order_primary`.`entity_id`
            )
            LEFT JOIN `' . $tablePrefix . 'sales_flat_order_address` AS `order_address` ON (
                `order_address`.`parent_id` = `order_primary`.`entity_id`
            ) AND (`order_address`.`address_type` = "billing")
            LEFT JOIN `' . $tablePrefix . 'directory_country_region` AS `customer_default_billing_state` ON (
                `customer_default_billing_state`.`region_id` = `order_address`.`region_id`
            )
            WHERE `order_primary`.status = "' . $idStatus . '"
                AND DATE_FORMAT(`order_primary`.`created_at`,"%Y-%m-%d") >= DATE_FORMAT(DATE_SUB("' . $dateReference . '", INTERVAL ' . ($nbDays + 14) . ' DAY),"%Y-%m-%d")
                AND DATE_FORMAT(`order_status`.`created_at`,"%Y-%m-%d") = DATE_FORMAT(DATE_SUB("' . $dateReference . '", INTERVAL ' . ($nbDays) . ' DAY),"%Y-%m-%d")
                AND ' . $timezonesWhere . '
                AND `order_last`.`entity_id` IS NULL
                AND `order_primary`.`store_id` IN ("' . implode('","', $scope->storeIds()) . '")
            GROUP BY `order_primary`.`customer_email`';

        $results = $readConnection->fetchAll($query);
        $return = array();
        if ($results && is_array($results) && sizeof($results)) {
            foreach ($results as $row) {
                $orderedProducts = self::productsOfOrder($scope, $row['quote_id']);
                $shippingNumbers = self::getShippingNumbersForOrderId($row['entity_id']);

                if (!empty($orderedProducts)) {
                    $return [] = array(
                        'currency' => $row['order_currency_code'],
                        'total_amount' => $row['base_grand_total'],
                        'products' => $orderedProducts,
                        'date_order' => $row['created_at'],
                        'id_order' => $row['increment_id'],
                        'customer' => self::getUser(($row['customer_id'] ? $row['customer_id'] : $row['customer_email'])),
                        'shipping_number' => $shippingNumbers,
                    );
                }
            }
        }
        return ($justCount ? array('count' => sizeof($return)) : $return);
    }

    private static function productsOfOrder(SPM_ShopyMind_Model_Scope $scope, $quoteId)
    {
        $helper = Mage::helper('shopymind');
        $emulatedEnvironment = $helper->startEmulatingScope($scope);
        $resultProducts = Mage::getModel('sales/quote')->load($quoteId)->getAllVisibleItems();
        if (empty($resultProducts) || !is_array($resultProducts)) {
            return array();
        }

        $ProductMapper = new SPM_ShopyMind_DataMapper_Product();
        $formatter = new SPM_ShopyMind_DataMapper_Pipeline(array(
            function (Mage_Sales_Model_Quote_Item $quoteItem) {
                return $quoteItem->getProduct();
            },
            array($ProductMapper, 'format'),
            SPM_ShopyMind_DataMapper_Scope::makeScopeEnricher($scope)
        ));
        $data = $formatter->format($resultProducts);

        $helper->stopEmulation($emulatedEnvironment);
        return $data;
    }

    /**
     * @param $orderId
     * @return array
     */
    public static function getShippingNumbersForOrderId($orderId)
    {
        $tracks = Mage::getModel('sales/order')->load($orderId)->getTracksCollection();
        $shippingNumbers = array();
        if ($tracks->count()) {
            foreach ($tracks as $track) {
                $shippingNumbers[] = $track->getNumber();
            }
        }
        return $shippingNumbers;
    }

    /**
     * Génération des codes de réduction
     *
     * @param array $voucherInfos
     * @param array $emails
     * @return array
     */
    public static function generateVouchers($voucherInfos, $emails, $idShop, $dynamicPrefix, $duplicateCode)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());

        $vouchers = array();
        if ($voucherInfos && is_array($voucherInfos) && $emails && is_array($emails) && sizeof($emails)) {
            $amount = ($voucherInfos['type'] == 'shipping' ? false : $voucherInfos['amount']);
            $amountCurrency = ($voucherInfos['type'] == 'amount' ? $voucherInfos ['amountCurrency'] : false);

            foreach ($emails as $email) {
                $voucher = self::generateVoucher($email['email'], $voucherInfos['type'], $amount, $amountCurrency, $voucherInfos['minimumOrder'], $voucherInfos['nbDayValidate'], $email['description'], $idShop, $dynamicPrefix, $duplicateCode);
                if (!$voucher) {
                    continue;
                }
                $vouchers[$email['email']] = $voucher;
            }
        }

        return $vouchers;
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
     * @param string $id_shop
     * @param string $dynamicProfix
     * @param int $duplicateCode
     * @return string
     */
    public static function generateVoucher($id_customer, $type, $amount = false, $amountCurrency = false, $minimumOrder = false, $nbDayValidate, $description, $id_shop, $dynamicPrefix, $duplicateCode) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());

        $GenerateVoucher = new SPM_ShopyMind_Action_GenerateVoucher($id_customer, $type, $amount, $amountCurrency, $minimumOrder, $nbDayValidate, $description, $id_shop, $dynamicPrefix, $duplicateCode);
        return $GenerateVoucher->process();
    }

    /**
     * Get customer groups
     *
     * The shop id parameter is not used in Magento as Magento
     * does not manage customer groups by store
     *
     * @param int $id_shop Magento store id
     *
     * @return array
     */
    public static function getCustomerGroups($id_shop) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        $return = array ();
        $results = Mage::getResourceModel('customer/group_collection')->toOptionArray();
        if ($results) {
            foreach ( $results as $row ) {
                $return[$row['value']] = $row['label'];
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
                if ($row['value'])
                    $return[$row['value']] = $row['label'];
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
    public static function startStoreEmulationByStoreId($store_id) {
        Mage::dispatchEvent('shopymind_start_emulation_before', array('storeId' => $store_id));

        if ($store_id == Mage::app()->getStore()->getId())
            return false;
        self::$appEmulation = Mage::getSingleton('core/app_emulation');
        self::$initialEnvironmentInfo = self::$appEmulation->startEnvironmentEmulation($store_id);
    }

    /**
     * Emulation de la langue de la boutique en fonction de l'ISO langue
     *
     * @param string $lang
     * @return boolean void
     */
    public static function startStoreEmulationByIsoLang($lang,$id_shop) {
        if (self::$appEmulation !== false)
            return;
        if (!empty($id_shop)) {
            list($scope, $website_id_needed) = explode('-', $id_shop);
        }
        $stores = Mage::app()->getStores();

        foreach ( $stores as $store ) {
            $store_id = $store->getId();
            $website_id = $store->getWebsite()->getId();
            $locale_store = Mage::getStoreConfig('general/locale/code', $store_id);
            if ($lang === substr($locale_store, 0, - 3) && (!isset($website_id_needed) || $website_id_needed == $website_id)) {
                self::startStoreEmulationByStoreId($store_id);
                break;
            }
        }
    }

    private static function startAdminStoreEmulation()
    {
        foreach (Mage::app()->getStores(true) as $store) {
            if ($store->isAdmin()) {
                self::startStoreEmulationByStoreId($store->getId());
                break;
            }
        }
    }

    public static function isStoreEmulated()
    {
        return (self::$appEmulation !== false);
    }

    /**
     * Retour à l'environnement normal
     *
     * @param string $lang
     * @return boolean void
     */
    public static function stopStoreEmulation() {
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
     * Récupération des langues disponibles
     * @param unknown $id_shop
     * @return mixed|multitype:unknown
     */
    public static function getLangs($id_shop) {
    	if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
    		return call_user_func_array(array (
    				'ShopymindClient_CallbackOverride',
    				__FUNCTION__
    		), func_get_args());
    	$return = array ();
    	if (!empty($id_shop)) {
    		list($scope, $website_id_needed) = explode('-', $id_shop);
    	}
    	$stores = Mage::app()->getStores();

    	foreach ( $stores as $store ) {
    		$store_id = $store->getId();
    		$website_id = $store->getWebsite()->getId();
    		$locale_store = Mage::getStoreConfig('general/locale/code', $store_id);
    		$iso_lang = substr($locale_store, 0, - 3);
    		if ((!isset($website_id_needed) || $website_id_needed == $website_id)) {
    			$return[$iso_lang] = $iso_lang;
    		}
    	}
    	return $return;
    }
    /**
     * Récupération des moyens de livraison
     * @return array
     */
    public static function getCarriers() {
    	if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
    		return call_user_func_array(array (
    				'ShopymindClient_CallbackOverride',
    				__FUNCTION__
    		), func_get_args());
    		$return = array ();
    		$results = Mage::getSingleton('shipping/config')->getActiveCarriers();

    		foreach($results as $_code => $_method)
    		{
    			if(!$_title = Mage::getStoreConfig("carriers/$_code/title"))
                    $_title = $_code;
                $return[$_code] = $_title . " ($_code)";
    		}
    		return $return;
    }
    /**
     * Récupération des boutiques
     * @return array
     */
    public static function getShops() {
    	if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
    		return call_user_func_array(array (
    				'ShopymindClient_CallbackOverride',
    				__FUNCTION__
    		), func_get_args());
    	$return = array ();
    	$websites = Mage::getModel('core/website')->getCollection();
    	foreach ($websites as $website){
    		$return[$website->getId()] = $website->getName();
    	}
    	return $return;
    }
    /**
     * Get test data
     *
     * @param int $id_shop Magento storeId
     * @param string|bool $lang Locale to use
     *
     * @return array
     */
    public static function getTestData($id_shop, $lang = false) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        if ($lang)
            self::startStoreEmulationByIsoLang($lang,$id_shop);
        $return = array ();
        // Lien vers panier
        $return ['link_cart'] = Mage::helper('shopymind')->getUrl('checkout/cart');
        // Article au hasard
        $return ['articles'] = self::getProducts($id_shop, $lang, false, true);
        if ($lang)
            self::stopStoreEmulation();
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
                $cartRule = Mage::getModel('salesrule/rule')->load($row['id']);
                $cartRule->delete();
            }
        }
        return true;
    }

    /**
     * Get products list
     * Both $products and $random cannot be false at the same time
     *
     * @param int $id_shop Magento store id
     * @param string $lang Locale to use
     * @param array|bool $products If array, list of product ids to fetch. If set to false, use the $random parameter
     * @param bool $random Fetch random products
     * @param int $maxProducts Maximum number of products to fetch if random
     *
     * @return array
     */
    public static function getProducts($id_shop, $lang, $products = false, $random = false, $maxProducts = 3) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__)) {
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        }
        /** @var SPM_ShopyMind_Helper_Data $helper */
        $helper = Mage::helper('shopymind');

        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId($id_shop, $lang);
        $emulatedEnvironment = $helper->startEmulatingScope($scope);

        $collection = array();
        if ($products) {
            $collection = Mage::getResourceModel('catalog/product_collection');
            $collection
                ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                ->addAttributeToFilter('entity_id', array (
                    'in' => $products
                ))
                ->addAttributeToFilter('status', array(
                        'in' => Mage::getModel('catalog/product_status')->getVisibleStatusIds())
                )
                ->addAttributeToFilter('visibility', array(
                        'in' => Mage::getModel('catalog/product_visibility')->getVisibleInCatalogIds())
                )
                ->setFlag('require_stock_items', true)
                ->getSelect();
            $collection->setPage(1, ($maxProducts ? $maxProducts : 3));
        } elseif ($random) {
            $collection = Mage::getResourceModel('catalog/product_collection');
            $collection
                ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                ->addAttributeToFilter('status', array(
                        'in' => Mage::getModel('catalog/product_status')->getVisibleStatusIds())
                )
                ->addAttributeToFilter('visibility', array(
                        'in' => Mage::getModel('catalog/product_visibility')->getVisibleInCatalogIds())
                )
                ->setFlag('require_stock_items', true)
                ->getSelect()->order('rand()');
            $collection->setPage(1, ($maxProducts ? $maxProducts : 3));
        }
        $scope->restrictProductCollection($collection);

        $ProductDataMapper = new SPM_ShopyMind_DataMapper_Product();
        $formatter = new SPM_ShopyMind_DataMapper_Pipeline(array(
            array($ProductDataMapper, 'format'),
            SPM_ShopyMind_DataMapper_Scope::makeScopeEnricher($scope)
        ));
        $result = $formatter->format(iterator_to_array($collection));

        $helper->stopEmulation($emulatedEnvironment);
        return array_values($result);
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
     * Number of orders passed by a customer, optionally on a given period
     *
     * @param int|string $customerIdOrEmail
     * @param string $sinceAgo Period to consider (in SQL DATE_SUB compatible format), optional (example: "1 YEAR")
     * @return int
     */
    public static function countCustomerOrder($customerIdOrEmail, $sinceAgo = null) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__)) {
            return call_user_func_array(array (
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        }

        $tablePrefix = Mage::getConfig()->getTablePrefix();
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');

        $conditions = self::ordersConditionsForCustomer($customerIdOrEmail, $sinceAgo);
        $query = sprintf(
            'SELECT COUNT(`entity_id`) AS `nbOrder` FROM `' . $tablePrefix . 'sales_flat_order` WHERE %s',
            implode(' AND ', $conditions)
        );
        $result = $read->fetchRow($query);

        return isset($result['nbOrder']) ? $result['nbOrder'] : 0;
    }

    /**
     * Total amount ordered by a client, optionally filtered on a given period
     *
     * @param int|string $customerIdOrEmail
     * @param string $sinceAgo Period to consider (in SQL DATE_SUB compatible format), optional (example: "1 YEAR")
     * @return int float
     */
    public static function sumCustomerOrder($customerIdOrEmail, $sinceAgo = null) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__)) {
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        }

        $tablePrefix = Mage::getConfig()->getTablePrefix();
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');

        $conditions = self::ordersConditionsForCustomer($customerIdOrEmail, $sinceAgo);
        $query = sprintf(
            'SELECT SUM(`base_total_invoiced`*base_to_order_rate) AS `sumOrder` FROM `' . $tablePrefix . 'sales_flat_order` WHERE %s',
            implode(' AND ', $conditions)
        );
        $result = $read->fetchRow($query);

        return isset($result['sumOrder']) ? $result['sumOrder'] : 0;
    }

    private static function ordersConditionsForCustomer($customerIdOrEmail, $sinceAgo)
    {
        $conditions = array('`base_total_invoiced` IS NOT NULL');
        if (!is_null($sinceAgo)) {
            $conditions[] = '`created_at` >= DATE_SUB("' . date('Y-m-d H:i:s') . '", INTERVAL ' . $sinceAgo . ')';
        }
        if (is_numeric($customerIdOrEmail)) {
            $conditions[] = '`customer_id` = ' . (int)$customerIdOrEmail;
        } else {
            $conditions[] = '`customer_email` = "' . $customerIdOrEmail . '"';
        }
        return $conditions;
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

        self::sendOrderToSPM($order);
    }

    /**
     * Envoi des ventes générées suite à une relance à ShopyMind
     *
     * @param Mage_Sales_Model_Order $order
     */
    public static function sendOrderToSPM($order) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__)) {
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        }
        self::startStoreEmulationByStoreId($order->getStoreId());

        include_once (Mage::getBaseDir('base') . '/lib/ShopymindClient/Bin/Notify.php');
        $params = self::formatOrderData($order);
        ShopymindClient_Bin_Notify::newOrder($params);
        self::stopStoreEmulation();
    }

    public static function saveOrder($order)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__))
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        self::startStoreEmulationByStoreId($order->getStoreId());
		
        include_once (Mage::getBaseDir('base') . '/lib/ShopymindClient/Bin/Notify.php');
        ShopymindClient_Bin_Notify::saveOrder($order->getId());
        self::stopStoreEmulation();
    }

    /**
     * Allow to retrieve the Magento customers list
     * This method is used for mailing or SMS campaign
     *
     * @param string $storeId Shopymind store id
     * @param string $start
     * @param int $limit
     * @param string $lastUpdate
     * @param boolean $justCount
     *
     * @return array $customers
     */
    public static function getContacts($storeId, $start, $limit, $lastUpdate, $justCount = false)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__)) {
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        }
        if ($justCount) {
            $count = 0;
        }
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId($storeId);

        $customerCollection = Mage::getModel('customer/customer')
            ->getCollection()
            ->addFieldToFilter('updated_at', array('gt' => $lastUpdate))
            ->addAttributeToSelect('entity_id');
        $scope->restrictCollection($customerCollection);

        $customerCollection->getSelect()->where('is_active = 1');

        if ($limit) {
            $customerCollection->getSelect()->limit($limit, $start);
        }

        $customers = array();
        foreach($customerCollection as $customer) {
            if ($justCount) {
                $count++;
            } else {
                $customers[] = array (
                    'customer' => self::getUser($customer['entity_id'])
                );
            }
        }

        //Add guest customer
        $guestCollection = Mage::getResourceModel('sales/order_collection')
            ->addAttributeToSelect('customer_email');

        $scope->restrictCollection($guestCollection);

        if (!is_null($lastUpdate)) {
            $guestCollection->addFieldToFilter('updated_at', array('gteq' => $lastUpdate));
        }

        $guestCollection->addFieldToFilter('customer_is_guest', 1);

        if (!is_null($limit)) {
            $guestCollection->getSelect()->limit($limit, $start);
        }


        foreach ($guestCollection as $customer) {
            if ($justCount) {
                $count++;
            } else {
                $customers[] = array (
                    'customer' => self::getUser($customer['customer_email'])
                );
            }
        }

        if ($justCount) {
            return $count;
        }

        return $customers;
    }

    private static function counterResponse(Varien_Data_Collection $collection)
    {
        return array(
            'count' => $collection->count()
        );
    }

    /**
     * Formate les données d'une commande dans le format attendu par le serveur de ShopyMind
     */
    private static function formatOrderData(Mage_Sales_Model_Order $order)
    {
        $OrderDataMapper = new SPM_ShopyMind_DataMapper_Order();

        return $OrderDataMapper->format($order);
    }

    /**
     * Method allowing to do a textual lookup for manufacturers matching a given search query
     *
     * @param $id_shop Id of the shop to restrict results to
     * @param bool $lang Allow to filter results for a store with a specific language
     * @param $search Text to match against manufacturer names. The search must be at least 3 chars long
     * @return array List of manufacturers (array('id' => 'xx', 'name' => 'yy')) ordered alphabetically
     */
    public static function findManufacturers($id_shop, $lang = false, $search)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__)) {
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        }

        if (strlen($search) < self::SEARCH_MIN_LENGTH) {
            return array();
        }
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', SPM_ShopyMind_Helper_Data::MANUFACTURER_ATTRIBUTE_CODE);

        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId($id_shop, $lang);
        $scope->restrictEavAttribute($attribute);

        $toShopyMindFormat = function($optionData) {
            return array(
                'id' => $optionData['value'],
                'name' => $optionData['label'],
            );
        };
        $matchesSearch = function($option) use ($search) {
            return stripos($option['name'], $search) !== false;
        };
        $options = array_filter(
            array_map($toShopyMindFormat, $attribute->getSource()->getAllOptions(false)),
            $matchesSearch
        );

        usort($options, function($optA, $optB) {
            return strcmp($optA['name'], $optB['name']);
        });
        return $options;
    }

    private static function returnCollectionDataOrCount(Varien_Data_Collection $collection, $justCount)
    {
        if ($justCount) {
            return self::counterResponse($collection);
        }

        return array_map(function ($customer) {
            return array('customer' => ShopymindClient_Callback::getUser($customer['entity_id']));
        }, $collection->getData());
    }

    /**
     * Allows to get customer emails from database.
     * This method is used to get emails when enabling retargeting. It will allow to know if the emails is real or not to not flag visitor as anonymous.
     * Once email is fetched, it will be hashed with md5
     *
     * @param string $id_shop storeId
     * @param int $start offset the result from the query
     * @param int $limit limit the result from the query.
     * @param string $lastUpdate date|datetime to narrow the query.
     *
     * @return array empty | array($customerId => $customerEmail);
     */
    public static function getExistingEmails($id_shop = null, $start = 0, $limit = null, $lastUpdate = null)
    {
        //Do we still need this ? It's looks like Prestashop Override mechanism
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__)) {
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        }

        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId($id_shop);

        $customersCollection = Mage::getModel('customer/customer')->getCollection()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('entity_id');

        $scope->restrictCollection($customersCollection);

        if (!is_null($lastUpdate)) {
            $customersCollection->addFieldToFilter('updated_at', array('gteq' => $lastUpdate));
        }

        if (!is_null($limit)) {
            $customersCollection->getSelect()->limit($limit, $start);
        }

        $customers = array();
        foreach ($customersCollection as $customer) {
            $customers[$customer->getId()] = $customer->getEmail();
        }

        //Add guest customer
        $guestCollection = Mage::getResourceModel('sales/order_collection')
        ->addAttributeToSelect('customer_email');

        $scope->restrictCollection($guestCollection);

        if (!is_null($lastUpdate)) {
            $guestCollection->addFieldToFilter('updated_at', array('gteq' => $lastUpdate));
        }

        $guestCollection->addFieldToFilter('customer_is_guest', 1);

        if (!is_null($limit)) {
            $guestCollection->getSelect()->limit($limit, $start);
        }


        foreach ($guestCollection as $customer) {
            if(!in_array($customer->getCustomerEmail(),$customers))
                $customers[$customer->getCustomerEmail()] = $customer->getCustomerEmail();
        }

        return $customers;
    }

    /**
     * Get customers who have not orders since $dateReference
     *
     * @param int $id_shop Magento store id
     * @param string $dateReference Date since when we should look for customers who have not ordered ; format: Y-m-d H:i:s
     * @param array $timezones Timezones to use
     * @param int $nbMonthsLastOrder How many months customers have not ordered since $dateReference
     * @param bool $relaunchOlder Force retrieval of customers that have not ordered since $dateReference - ($nbMonthsLastOrder + $relaunchOlder) months
     * @param bool $justCount
     *
     * @return bool|array|int
     */
    public static function getInactiveClients($id_shop, $dateReference, $timezones, $nbMonthsLastOrder, $relaunchOlder = false, $justCount = false) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__)) {
            return call_user_func_array(array (
                    'ShopymindClient_CallbackOverride',
                    __FUNCTION__
            ), func_get_args());
        }

        if (empty($timezones)) {
            return false;
        }

        $collection = Mage::getResourceModel('sales/order_collection')->addAttributeToSelect('customer_id')->addAttributeToSelect('customer_email')->addAttributeToFilter('main_table.status', array (
                'in' => array (
                        'processing',
                        'complete'
                )
        ));

        SPM_ShopyMind_Model_Scope::fromShopymindId($id_shop)->restrictCollection($collection, 'main_table.store_id');

        $collection->getSelect()->distinct(true);

        self::restrictOrdersCollectionBillingAddressesToTimezones($collection, $timezones);

        $collection->getSelect()->joinLeft(array (
                'recent_orders' => Mage::getSingleton('core/resource')->getTableName('sales_flat_order')
        ), '(main_table.customer_id = recent_orders.customer_id) AND DATE(recent_orders.created_at) >= DATE_SUB("' . $dateReference . '", INTERVAL ' . (int) ($nbMonthsLastOrder) . ' MONTH) AND recent_orders.status IN ("processing", "complete")', null)->where('recent_orders.entity_id IS NULL');

        $collection->getSelect()->joinLeft(array (
                'orders_at_date' => Mage::getSingleton('core/resource')->getTableName('sales_flat_order')
        ), '(main_table.customer_id = orders_at_date.customer_id) AND DATE_FORMAT(orders_at_date.created_at, "%Y-%m-%d") = DATE_FORMAT(DATE_SUB(DATE_SUB("' . $dateReference . '", INTERVAL ' . (int) $nbMonthsLastOrder . ' MONTH), INTERVAL 1 DAY), "%Y-%m-%d") AND orders_at_date.status IN ("processing", "complete")', null)->where('orders_at_date.entity_id IS NOT NULL');

        if ($justCount) {
            return self::counterResponse($collection);
        }
        $customers = array();
        foreach ( $collection as $order ) {

            $customers [] = array (
                    'customer' => self::getUser(($order ['customer_id'] ? $order ['customer_id'] : $order ['customer_email']))
            );
        }

        return $customers;
    }

    private static function restrictOrdersCollectionBillingAddressesToTimezones(Varien_Data_Collection_Db $collection, array $timezones)
    {
        $timezonesWhere = self::generateTimezonesWhere($timezones, 'sales/order_address', 'country_id', 'directory/country_region', 'code');
        if (!empty($timezonesWhere)) {
            $collection->getSelect()
                ->joinLeft(array('sales/order_address' => Mage::getSingleton('core/resource')->getTableName('sales_flat_order_address')), '`sales/order_address`.parent_id = main_table.entity_id AND `sales/order_address`.address_type = "billing"', null)
                ->joinLeft(array('directory/country_region' => Mage::getSingleton('core/resource')->getTableName('directory_country_region')), '`directory/country_region`.region_id = `sales/order_address`.region_id', null)
				->where($timezonesWhere);
        }
    }

    public static function findProducts($id_shop, $lang = false, $search)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__)) {
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        }

        if (strlen($search) < self::SEARCH_MIN_LENGTH) {
            return array();
        }

        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection
            ->addAttributeToSelect('name')
            ->addAttributeToFilter(array(
                array(
                    'attribute' => 'name',
                    'like' => '%' . $search . '%'
                ),
                array(
                    'attribute' => 'sku',
                    'like' => '%' . $search . '%'
                ),
            ))
            ->addAttributeToSort('name', Varien_Data_Collection_Db::SORT_ORDER_ASC)
            ->addAttributeToFilter('status', array(
                'in' => Mage::getModel('catalog/product_status')->getVisibleStatusIds())
            )
            ->addAttributeToFilter('visibility', array(
                'in' => Mage::getModel('catalog/product_visibility')->getVisibleInCatalogIds())
            )
            ->setPage(1, 100) // limit to prevent abuses
        ;

        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId($id_shop, $lang);
        $scope->restrictProductCollection($collection);

        $products = array();
        foreach ($collection as $product) {
            /** @var Mage_Catalog_Model_Product $product */
            $data = array(
                'id' => $product->getId(),
                'name' => $product->getName(),
                'combinations' => self::combinationsOfProduct($product)
            );
            $products[] = $data;
        }
        return $products;
    }

    private static function combinationsOfProduct(Mage_Catalog_Model_Product $product)
    {
        $formatter = function ($childProduct) {
            return array(
                'id' => $childProduct->getId(),
                'name' => $childProduct->getName()
            );
        };
        return Mage::helper('shopymind')->formatCombinationsOfProduct($product, $formatter, array('name'));
    }

    public static function findCategories($id_shop, $lang = false, $search) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__)) {
            return call_user_func_array(array(
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        }

        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId($id_shop);
        if ($lang) {
            self::startStoreEmulationByIsoLang($lang,$id_shop);
        } elseif ($id_shop) {
            $storeIds = $scope->storeIds();
            if (!empty($storeIds)) {
                self::startStoreEmulationByStoreId($storeIds[0]);
            }
        } else {
           self::startAdminStoreEmulation();
        }

        if (strlen($search) < self::SEARCH_MIN_LENGTH) {
            return array();
        }

        $collection = Mage::getModel('catalog/category')->getCollection();
        $collection
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('name', array('like' => '%' . $search . '%'))
            ->addIsActiveFilter()
            ->addOrderField('name')
        ;

        $scope->restrictCategoryCollection($collection);

        $categories = array();
        foreach($collection as $category) {
            $categories[] = array(
                'id' => $category->getId(),
                'name' => $category->getName()
            );
        }

        self::stopStoreEmulation();

        return $categories;
    }


    /**
     * Returns the details of the requested cart(s)
     *
     * @param integer $id_shop Shop id
     * @param integer|array $id_cart Cart id(s) to get details from
     *
     * @return array
     */
    public static function getCarts($id_shop, $id_cart)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__)) {
            return call_user_func_array(array (
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        }

        $collection = Mage::getResourceModel('sales/quote_collection')
            ->addFieldToFilter('entity_id', array('in' => (array) $id_cart));

        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId($id_shop);
        $scope->restrictCollection($collection);

        $results = array();
		$countIds = count($scope->storeIds());
		
        foreach ($collection as $quote) {
			$cartProducts = self::productsOfCart($scope, ($countIds > 1) ? $quote->getData()['store_id'] : null, $quote->getId());
            $results[] = array(
                'sum_cart' => ($quote->getBaseGrandTotal() / $quote->getStoreToBaseRate()),
                'currency' => $quote->getBaseCurrencyCode(),
                'tax_rate' => $quote->getStoreToBaseRate(),
                'id_cart' => $quote->getId(),
                'date_cart' =>$quote->getCreatedAt(),
                'date_upd' => $quote->getUpdatedAt(),
                'link_cart' => Mage::helper('shopymind')->getUrl('checkout/cart'),
                'products' => $cartProducts,
                'customer' => self::getUser(($quote->getCustomerId() ? $quote->getCustomerId() : $quote->getCustomerEmail()), true)
            );
        }

        return $results;
    }


    /**
     * Create customer
     *
     * @param integer $id_shop
     * @param string $lang
     * @param array $customer_infos
     * @return boolean|array
     */
    public static function createCustomer($id_shop, $lang, $customer_infos)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__)) {
            return call_user_func_array(array (
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        }
        $return = true;

        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId($id_shop,$lang);
        $stores = $scope->stores();

        if(is_array($stores) && count($stores)) {
            $store = $stores[0];
        }
        else {
            $scope = SPM_ShopyMind_Model_Scope::fromShopymindId($id_shop);
            $stores = $scope->stores();
            if(is_array($stores) && count($stores))
                $store = $stores[0];
            else $return = false;
        }
        if($return) {
            $customer = Mage::getModel("customer/customer");
            $customer
            ->setFirstname($customer_infos['firstName'])
            ->setLastname($customer_infos['lastName'])
            ->setEmail($customer_infos['email'])
            ->setPassword($customer_infos['password'])
            ->setDob($customer_infos['dob'])
            ->getGender(($customer_infos['gender'] == 'mr'  ? 1 : 2));

            $customer
            ->setStore($store)
            ->setWebsiteId($store->getWebsiteId());

            try{
                $customer->save();
                $customer->setConfirmation(null);
                $customer->save();
                $customer->sendNewAccountEmail('registered','',$store->getId());
            }
            catch (Exception $e) {
                Mage::log($e->getMessage(), Zend_Log::ERR);
                $return = false;
            }
        }

        return ($return ? self::getUser($customer->getId()) : false);
    }

    public static function getOrder($idOrder)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__)) {
            return call_user_func_array(array (
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        }
        return self::formatOrderData(Mage::getModel('sales/order')->load($idOrder));
    }

    public static function syncCustomers($id_shop, $start, $limit, $lastUpdate, $idCustomer = false, $justCount = false)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__)) {
            return call_user_func_array(array (
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        }

        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId($id_shop);
        $SyncCustomersAction = new SPM_ShopyMind_Action_SyncCustomers($scope, $start, $limit, $lastUpdate, $idCustomer, $justCount);
        return $SyncCustomersAction->process();
    }

    public static function syncProducts($id_shop, $start, $limit, $lastUpdate, $idProduct= false, $justCount = false)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__)) {
            return call_user_func_array(array (
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        }

        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId($id_shop);
        $SyncProductsAction = new SPM_ShopyMind_Action_SyncProducts($scope, $start, $limit, $lastUpdate, $idProduct, $justCount);
        return $SyncProductsAction->process();
    }

    public static function syncProductsCategories($id_shop, $start, $limit, $lastUpdate, $idCategory = false, $justCount = false)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__)) {
            return call_user_func_array(array (
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        }

        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId($id_shop);
        $SyncProductCategoriesAction = new SPM_ShopyMind_Action_SyncProductCategories($scope, $start, $limit, $lastUpdate, $idCategory, $justCount);
        return $SyncProductCategoriesAction->process();
    }

    public static function syncOrders($id_shop, $start, $limit, $lastUpdate, $orderId = false, $justCount = false)
    {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__)) {
            return call_user_func_array(array (
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        }

        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId($id_shop);
        $SyncCustomersAction = new SPM_ShopyMind_Action_SyncOrders($scope, $start, $limit, $lastUpdate, $orderId, $justCount);
        return $SyncCustomersAction->process();
    }
}
