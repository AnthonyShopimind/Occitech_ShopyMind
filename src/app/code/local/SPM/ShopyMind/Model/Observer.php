<?php
/**
 * SPM_ShopyMind_Model_Observer
 *
 * @package     SPM_ShopyMind
 * @copyright   Copyright (c) 2014 - ShopyMind SARL (http://www.shopymind.com)
 * @license     New BSD license (http://license.shopymind.com)
 * @author      Couvert Jean-Sébastien <js.couvert@shopymind.com>
 * @version     $Id Observer.php 2014-12-17$
 */
class SPM_ShopyMind_Model_Observer extends Varien_Event_Observer {
    public function __construct() {
    }
    public function newOrderObserver($observer) {
        try {
            $m = new Mage();
            $mageVersion = $m->getVersion();
            $order = $observer->getEvent()->getInvoice()->getOrder();
            ShopymindClient_Callback::checkNewOrder($order);
        } catch ( Exception $e ) {
        }
    }
    public static function getUserLocale($id_customer, $store_id) {
        $locale_shop = Mage::getStoreConfig('general/locale/code', $store_id);
        $customer = Mage::getModel('customer/customer')->load($id_customer);
        $defaultBilling = $customer->getDefaultBillingAddress();
        if ($defaultBilling)
            return substr($locale_shop, 0, 3) . $defaultBilling->getCountry();
        return $locale_shop;
    }
}
?>
