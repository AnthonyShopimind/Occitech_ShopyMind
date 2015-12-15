<?php

class SPM_ShopyMind_DataMapper_Customer
{
    public function format($user)
    {
        $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($user['email']);

        return array (
            'id_customer' => $user['entity_id'],
            'shop_id_shop' => $user['store_id'],
            'optin' => $subscriber->isSubscribed(),
            'customer_since' => $user['created_at'],
            'last_name' => $user['lastname'],
            'first_name' => $user['firstname'],
            'email_address' => $user['email'],
            'phone1' => (isset($user['phone']) && $user['phone'] ? $user['phone'] : ''),
            'phone2' => '',
            'gender' => (isset($user['gender_id']) && ($user['gender_id'] == 1 || $user['gender_id'] == 2) ? $user['gender_id'] : 0),
            'birthday' => (isset($user['birthday']) ? $user['birthday'] : 0),
            'locale' => $this->getUserLocale($user['entity_id'], $user['store_id'], $user['country_code']),
            'region' => $user['region_code'],
            'postcode' => $user['postcode'],
            'date_last_order' => $this->getDateLastOrder($user['entity_id']),
            'nb_order' => $this->countCustomerOrder($user['entity_id'], null),
            'sum_order' => $this->sumCustomerOrder($user['entity_id']),
            'nb_order_year' => $this->countCustomerOrder($user['entity_id'], '1 YEAR'),
            'sum_order_year' => $this->sumCustomerOrder($user['entity_id'], '1 YEAR'),
            'groups' => array ($user['group_id']),
        );
    }

    private function getUserLocale($id_customer, $store_id, $country_code = false) {
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

    private function getDateLastOrder($id_customer) {
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

    private function countCustomerOrder($customerIdOrEmail, $sinceAgo = null) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__)) {
            return call_user_func_array(array (
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        }

        $tablePrefix = Mage::getConfig()->getTablePrefix();
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');

        $conditions = $this->ordersConditionsForCustomer($customerIdOrEmail, $sinceAgo);
        $query = sprintf(
            'SELECT COUNT(`entity_id`) AS `nbOrder` FROM `' . $tablePrefix . 'sales_flat_order` WHERE %s',
            implode(' AND ', $conditions)
        );
        $result = $read->fetchRow($query);

        return isset($result['nbOrder']) ? $result['nbOrder'] : 0;
    }

    private function sumCustomerOrder($customerIdOrEmail, $sinceAgo = null) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__)) {
            return call_user_func_array(array (
                'ShopymindClient_CallbackOverride',
                __FUNCTION__
            ), func_get_args());
        }

        $tablePrefix = Mage::getConfig()->getTablePrefix();
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');

        $conditions = $this->ordersConditionsForCustomer($customerIdOrEmail, $sinceAgo);
        $query = sprintf(
            'SELECT SUM(`base_total_invoiced`*base_to_order_rate) AS `sumOrder` FROM `' . $tablePrefix . 'sales_flat_order` WHERE %s',
            implode(' AND ', $conditions)
        );
        $result = $read->fetchRow($query);

        return isset($result['sumOrder']) ? $result['sumOrder'] : 0;
    }

    private function ordersConditionsForCustomer($customerIdOrEmail, $sinceAgo)
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
}
