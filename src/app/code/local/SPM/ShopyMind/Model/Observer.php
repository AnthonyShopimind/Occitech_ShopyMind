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

    const REQUIRED = 1;
    const OPTIONAL = 0;

    const LEGACY_REQUIRED = 'yes';
    const LEGACY_OPTIONAL = 'no';

    const CUSTOMER_SHOW_DOB_CONFIG_PATH = 'customer/address/dob_show';
    const OPTIONAL_CUSTOMER_DOB = 'opt';
    const REQUIRED_CUSTOMER_DOB = 'req';

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

    public function adminSystemConfigChangedSectionShopymindConfiguration(Varien_Event_Observer $observer)
    {
        $this->updateDateOfBirthCustomerAttributeFrom($observer);
    }

    private function updateDateOfBirthCustomerAttributeFrom(Varien_Event_Observer $observer)
    {
        list($scope, $scopeId) = $this->getScopeFromEvent($observer);
        $isBirthDateRequired =  $this->isDateOfBirthRequiredForModule($observer);
        $showDateOfBirth = $isBirthDateRequired ? self::REQUIRED_CUSTOMER_DOB : self::OPTIONAL_CUSTOMER_DOB;

        $Config = Mage::getModel('core/config');
        $Config->saveConfig(self::CUSTOMER_SHOW_DOB_CONFIG_PATH, $showDateOfBirth, $scope, $scopeId);
        $Config->reinit();

        $EavEntity = Mage::getModel('eav/entity_setup', 'core_setup');
        $customerEntityTypeId = Mage::getModel('customer/customer')->getEntityTypeId();
        $dobSettings = array(
            'is_required' => $isBirthDateRequired ? self::REQUIRED : self::OPTIONAL,
            'is_visible' => true,
        );

        $EavEntity->updateAttribute($customerEntityTypeId, 'dob', $dobSettings);
        Mage::app()->reinitStores();
    }

    private function getScopeFromEvent(Varien_Event_Observer $observer)
    {
        if (!is_null($observer->getStore())) {
            $scope = 'stores';
            $scopeId = Mage::getModel('core/store')->load($observer->getStore(), 'code')->getId();
        } else {
            $scope = 'default';
            $scopeId = 0;
        }
        return array($scope, $scopeId);
    }

    public function isDateOfBirthRequiredForModule(Varien_Event_Observer $observer)
    {
        $store = null;
        if (!is_null($observer->getStore())) {
            $store = Mage::getModel('core/store')->load($observer->getStore(), 'code');
        }

        return Mage::getStoreConfig('shopymind/configuration/birthrequired', $store) == self::REQUIRED;
    }

    public function isMultiStore()
    {
        $activeStores = array_filter(Mage::app()->getStores(), function($store) {
            return $store->getIsActive();
        });

        return count($activeStores) > 1;
    }
}
