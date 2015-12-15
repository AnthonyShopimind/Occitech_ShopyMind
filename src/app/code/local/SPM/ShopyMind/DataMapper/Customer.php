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
            'locale' => ShopymindClient_Callback::getUserLocale($user['entity_id'], $user['store_id'], $user['country_code']),
            'region' => $user['region_code'],
            'postcode' => $user['postcode'],
            'date_last_order' => ShopymindClient_Callback::getDateLastOrder($user['entity_id']),
            'nb_order' => ShopymindClient_Callback::countCustomerOrder($user['entity_id'], null),
            'sum_order' => ShopymindClient_Callback::sumCustomerOrder($user['entity_id']),
            'nb_order_year' => ShopymindClient_Callback::countCustomerOrder($user['entity_id'], '1 YEAR'),
            'sum_order_year' => ShopymindClient_Callback::sumCustomerOrder($user['entity_id'], '1 YEAR'),
            'groups' => array ($user['group_id']),
        );
    }
}
