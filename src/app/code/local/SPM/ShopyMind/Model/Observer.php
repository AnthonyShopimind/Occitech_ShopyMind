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

    public function newOrderObserver($observer) {
        try {
            $order = $observer->getEvent()->getInvoice()->getOrder();
            ShopymindClient_Callback::checkNewOrder($order);
        } catch ( Exception $e ) {
            Mage::log($e->getMessage(), Zend_Log::ERR);
        }
    }
    public static function getUserLocale($id_customer, $store_id) {
        $locale_shop = Mage::getStoreConfig('general/locale/code', $store_id);
        $customer = Mage::getModel('customer/customer')->load($id_customer);
        $defaultBilling = $customer->getDefaultBillingAddress();
        if ($defaultBilling) {
            return substr($locale_shop, 0, 3) . $defaultBilling->getCountry();
        }
        return $locale_shop;
    }

    public function adminSystemConfigChangedSectionShopymindConfiguration(Varien_Event_Observer $observer)
    {
        $this->updateDateOfBirthCustomerAttributeFrom($observer);
        $this->sendInformationForShopyMindForStore($observer->getStore());
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

        $isDateOfBirthRequired = Mage::getStoreConfig('shopymind/configuration/birthrequired', $store);
        return ($isDateOfBirthRequired == self::REQUIRED) || ($isDateOfBirthRequired == self::LEGACY_REQUIRED);
    }

    public function isMultiStore()
    {
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId(false);
        $storeLangCodes = array_map(function (Mage_Core_Model_Store $store) {
            return $store->getConfig('general/locale/code');
        }, $scope->stores());

        return array_unique($storeLangCodes) !== $storeLangCodes;
    }

    public function sendInformationForShopyMindForStore($storeCode = null)
    {
        if (!$this->hasShopyMindClientConfiguration()) {
            return;
        }

        if (!is_null($storeCode)) {
            $currentStore =  Mage::getModel('core/store')->load($storeCode, 'code');
        } else {
            $currentStore =  Mage::app()->getDefaultStoreView();
        }

        $this->dispatchToShopyMind(
            Mage::getStoreConfig('shopymind/configuration/apiidentification', $currentStore),
            Mage::getStoreConfig('shopymind/configuration/apipassword', $currentStore),
            substr(Mage::app()->getLocale()->getDefaultLocale(),0,2),
            Mage::getStoreConfig('currency/options/default', $currentStore),
            Mage::getUrl('contacts'),
            Mage::getStoreConfig('general/store_information/phone', $currentStore),
            Mage::getStoreConfig('general/locale/timezone', $currentStore),
            $this->isMultiStore(),
            $currentStore->getId()
        );
    }

    public function dispatchToShopyMind($apiIdentifiant, $apiPassword, $defaultLanguage, $defaultCurrency, $contactPageUrl, $phoneNumber, $timezone, $isMultiStore, $storeId)
    {
        $configurationClient = $this->getShopyMindClientConfiguration($apiIdentifiant, $apiPassword, $defaultLanguage, $defaultCurrency, $contactPageUrl, $phoneNumber, $timezone, $isMultiStore, $storeId);

        if (!$configurationClient->testConnection()) {
            Mage::throwException($this->__('Error when test connection'));
        } else {
            // Connexion au serveur et sauvegarde des informations
            $connect = $configurationClient->connectServer();
            if ($connect !== true) {
                Mage::throwException($this->__('Error when connect to server'));
            } else {
                $message = $this->__('Your form has been submitted successfully.');
                Mage::getSingleton('adminhtml/session')->addSuccess($message);
            }
        }
    }

    private function hasShopyMindClientConfiguration()
    {
        return file_exists(Mage::getBaseDir('base') . '/lib/ShopymindClient/Bin/Configuration.php');
    }

    /**
     * @param $apiIdentifiant
     * @param $apiPassword
     * @param $defaultLanguage
     * @param $defaultCurrency
     * @param $contactPageUrl
     * @param $phoneNumber
     * @param $timezone
     * @param $isMultiStore
     * @param $storeId
     * @return ShopymindClient_Bin_Configuration
     */
    private function getShopyMindClientConfiguration($apiIdentifiant, $apiPassword, $defaultLanguage, $defaultCurrency, $contactPageUrl, $phoneNumber, $timezone, $isMultiStore, $storeId)
    {
        return ShopymindClient_Bin_Configuration::factory($apiIdentifiant, $apiPassword, $defaultLanguage, $defaultCurrency, $contactPageUrl, $phoneNumber, $timezone, $isMultiStore, $storeId);
    }
}
