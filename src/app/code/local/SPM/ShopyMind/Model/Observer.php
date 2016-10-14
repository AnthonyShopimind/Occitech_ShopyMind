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

    /**
     * Vérifie si une synchro peut être effectuée en respectant un délai minimum
     * @param string $type
     */
    public static function checkSyncDate($type) {
		return true;
		
        $lastCall = Mage::getConfig()->getNode('shopymind/configuration/lastsync_'.$type);
        $pass = true;

        if (!is_null($lastCall) && $lastCall != '') {
            $datetime1 = new DateTime();
            $datetime2 = new DateTime($lastCall);
            $interval = ($datetime1->format('U')-$datetime2->format('U'))/60;

            if($interval < 1) {
                $pass = false;
            }
        }

        return $pass;
    }

    /**
     * Enregistre la dernière date de synchro pour le type passé
     * @param string $type
     */
    public static function setSyncDate($type) {
        $data = new Mage_Core_Model_Config();
        $data->saveConfig('shopymind/configuration/lastsync_'.$type, date('Y-m-d H:i:s'));
    }

    public function orderPlaceObserver(Varien_Event_Observer $observer)
    {
        try {		
			$order = $observer->getEvent()->getData('order');
			if ($order->hasStatus()) {
				ShopymindClient_Callback::saveOrder($order);
				self::setSyncDate('order');
			}
        } catch ( Exception $e ) {
            Mage::log($e->getMessage(), Zend_Log::ERR);
        }
    }

    public function orderUpdateObserver(Varien_Event_Observer $observer)
    {
        try {
            $chk = self::checkSyncDate('order');
            if($chk) {
                $order = $observer->getOrder();
                if ($order->hasStatus()) {
                    //On vérifie que la commande n'est pas trop ancienne
                    $date = new DateTime();
                    $date->sub(new DateInterval('P3M'));
                    if($date->format('Y-m-d H:i:s') > $order->created_at)
                        return;

                    ShopymindClient_Callback::saveOrder($order);
                    self::setSyncDate('order');
                }
            }
        } catch ( Exception $e ) {
            Mage::log($e->getMessage(), Zend_Log::ERR);
        }
    }

    public static function getUserLocale($id_customer, $store_id)
    {
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

        if($scope->getId() == 0) {
            $multishop = 0;
        }
        else {
            $multishop = true/*$this->isMultiStore()*/;
        }
        $locale = $scope->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE);
        $this->dispatchToShopyMind(
            (string) $scope->getConfig('shopymind/configuration/apiidentification'),
            (string) $scope->getConfig('shopymind/configuration/apipassword'),
            (string) substr($locale, 0, 2),
            (string) $scope->getConfig('currency/options/default'),
            (string) Mage::getUrl('contacts'),
            (string) $scope->getConfig('general/store_information/phone'),
            (string) $scope->getConfig('general/locale/timezone'),
            (string) $multishop,
            (string) $scope->shopyMindId()
        );
    }

    public function dispatchToShopyMind($apiIdentifiant, $apiPassword, $defaultLanguage, $defaultCurrency, $contactPageUrl, $phoneNumber, $timezone, $isMultiStore, $storeId)
    {
        $configurationClient = $this->getShopyMindClientConfiguration($apiIdentifiant, $apiPassword, $defaultLanguage, $defaultCurrency, $contactPageUrl, $phoneNumber, $timezone, $isMultiStore, $storeId);

        if (!$configurationClient->testConnection()) {
            Mage::throwException(Mage::helper('shopymind')->__('Error when testing connection'));
        } else {
            // Connexion au serveur et sauvegarde des informations
            $connect = $configurationClient->connectServer();
            if ($connect !== true) {
                Mage::throwException(Mage::helper('shopymind')->__('Error when connecting to the server'));
            } else {
                $message = Mage::helper('shopymind')->__('Your form has been submitted successfully.');
                Mage::getSingleton('adminhtml/session')->addSuccess($message);
            }
        }
    }

    /**
     * @event catalog_product_save_after
     */
    public function saveProduct(Varien_Event_Observer $observer)
    {
		try {
			$chk = self::checkSyncDate('product');
			if($chk) {
				ShopymindClient_Bin_Notify::saveProduct($observer->getEvent()->getProduct()->getId());
				self::setSyncDate('product');
			}
		}
		catch(Exception $e) {
			
		}
    }

    /**
     * @event catalog_product_delete_after_done
     */
    public function deleteProduct(Varien_Event_Observer $observer)
    {
		try {
			$params = array('id_product' => $observer->getEvent()->getProduct()->getId());
			ShopymindClient_Bin_Notify::deleteProduct($params);
		}
		catch(Exception $e) {
			
		}
    }

    /**
     * @event catalog_category_save_after
     */
    public function saveProductCategory(Varien_Event_Observer $observer)
    {
		try {
			$chk = self::checkSyncDate('productCategory');
			if($chk) {
				$category = $observer->getEvent()->getCategory();
				if ($category->hasInitialSetupFlag()) {
					return; // Prevent triggering notifications during the setup of a Magento store (or during tests setup). See magento_src/app/code/core/Mage/Catalog/data/catalog_setup/data-install-1.6.0.0.php:52
				}
				ShopymindClient_Bin_Notify::saveProductCategory($category->getId());
				self::setSyncDate('productCategory');
			}
		}
		catch(Exception $e) {
			
		}
    }

    /**
     * @event catalog_category_delete_after
     */
    public function deleteProductCategory(Varien_Event_Observer $observer)
    {
		try {
			$params = array('id_category' => $observer->getEvent()->getCategory()->getId());
			ShopymindClient_Bin_Notify::deleteProductCategory($params);
		}
		catch(Exception $e) {
			
		}
    }


    /**
     * @event customer_address_save_after
     * @event customer_address_delete_after
     */
    public function saveCustomerAddress(Varien_Event_Observer $observer)
    {
        try {
			$chk = self::checkSyncDate('customer');
			if($chk) {
				ShopymindClient_Bin_Notify::saveCustomer($observer->getCustomerAddress()->getCustomerId());
				self::setSyncDate('customer');
			}
		}
		catch(Exception $e) {
			
		}
    }

    /**
     * @event customer_save_after
     */
    public function saveCustomer(Varien_Event_Observer $observer)
    {
		try {
			$chk = self::checkSyncDate('customer');
			if($chk) {
				ShopymindClient_Bin_Notify::saveCustomer($observer->getEvent()->getCustomer()->getId());
				self::setSyncDate('customer');
			}
		}
		catch(Exception $e) {
			
		}
    }

    /**
     * @event customer_delete_after
     */
    public function deleteCustomer(Varien_Event_Observer $observer)
    {
		try {
			$params = array('id_customer' => $observer->getEvent()->getCustomer()->getId());
			ShopymindClient_Bin_Notify::deleteCustomer($params);
		}
		catch(Exception $e) {
			
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
