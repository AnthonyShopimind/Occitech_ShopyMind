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
        $scope = SPM_ShopyMind_Model_Scope::fromMagentoCodes($observer->getWebsite(), $observer->getStore());

        $this->updateDateOfBirthCustomerAttributeFrom($scope);
        $this->sendInformationToShopyMindFor($scope);
    }

    public function updateDateOfBirthCustomerAttributeFrom(SPM_ShopyMind_Model_Scope $scope)
    {
        $isBirthDateRequired = $this->isDateOfBirthRequiredForModule($scope);

        $scope->saveConfig(
            self::CUSTOMER_SHOW_DOB_CONFIG_PATH,
            $isBirthDateRequired ? self::REQUIRED_CUSTOMER_DOB : self::OPTIONAL_CUSTOMER_DOB
        );

        $EavEntity = Mage::getModel('eav/entity_setup', 'core_setup');
        $customerEntityTypeId = Mage::getModel('customer/customer')->getEntityTypeId();
        $dobSettings = array(
            'is_required' => $isBirthDateRequired ? self::REQUIRED : self::OPTIONAL,
            'is_visible' => true,
        );
        $EavEntity->updateAttribute($customerEntityTypeId, 'dob', $dobSettings);

        Mage::app()->reinitStores();
    }

    public function isDateOfBirthRequiredForModule(SPM_ShopyMind_Model_Scope $scope)
    {
        $isDateOfBirthRequired = $scope->getConfig('shopymind/configuration/birthrequired');
        return ($isDateOfBirthRequired == self::REQUIRED) || ($isDateOfBirthRequired == self::LEGACY_REQUIRED);
    }

    public function isMultiStore()
    {
        $scope = SPM_ShopyMind_Model_Scope::fromMagentoCodes(null, null);
        $storeLangCodes = array_map(function (Mage_Core_Model_Store $store) {
            return $store->getConfig('general/locale/code');
        }, $scope->stores());

        return array_unique($storeLangCodes) !== $storeLangCodes;
    }

    public function sendInformationToShopyMindFor(SPM_ShopyMind_Model_Scope $scope)
    {
        if (!$this->hasShopyMindClientConfiguration()) {
            return;
        }

        $locale = $scope->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE);
        $this->dispatchToShopyMind(
            $scope->getConfig('shopymind/configuration/apiidentification'),
            $scope->getConfig('shopymind/configuration/apipassword'),
            substr($locale, 0, 2),
            $scope->getConfig('currency/options/default'),
            Mage::getUrl('contacts'),
            $scope->getConfig('general/store_information/phone'),
            $scope->getConfig('general/locale/timezone'),
            $this->isMultiStore(),
            $scope->shopyMindId()
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
