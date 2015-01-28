<?php
/**
 * SPM_ShopyMind_Adminhtml_ConfigurationController
 *
 * @package     SPM_ShopyMind
 * @copyright   Copyright (c) 2014 - ShopyMind SARL (http://www.shopymind.com)
 * @license     New BSD license (http://license.shopymind.com)
 * @author      Couvert Jean-Sébastien <js.couvert@shopymind.com>
 * @version     $Id ConfigurationController.php 2014-12-17$
 */
class SPM_ShopyMind_Adminhtml_ConfigurationController extends Mage_Adminhtml_Controller_Action {
    public function indexAction() {
        if (! function_exists('curl_init')) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Shopymind needs the PHP Curl extension, please ask your hosting provider to enable it prior to use this module.'));
        }
        $this->loadLayout()->renderLayout();
    }
    public function postAction() {
        $post = $this->getRequest()->getPost();
        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            $config = new Mage_Core_Model_Config();
            /* here's your form processing */
            $config->saveConfig('shopymind/configuration/apiidentification', $_POST ['SPMForm'] ['apiIdentification'], 'default', 0);
            $config->saveConfig('shopymind/configuration/apipassword', $_POST ['SPMForm'] ['apiPassword'], 'default', 0);
            $config->saveConfig('shopymind/configuration/birthrequired', $_POST ['SPMForm'] ['birthRequired'], 'default', 0);
            if ($_POST ['SPMForm'] ['birthRequired'] == 'yes') {

                $settings = array (
                        'is_required' => 1,
                        'is_visible' => true
                );
                $config->saveConfig('customer/address/dob_show', 'req', 'default', 0);
            } else {
                $settings = array (
                        'is_required' => 0,
                        'is_visible' => true
                );
                $config->saveConfig('customer/address/dob_show', 'opt', 'default', 0);
            }
            $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
            $AttrCode = 'dob';
            $setup->updateAttribute('1', $AttrCode, $settings);
            $config->reinit();
            Mage::app()->reinitStores();
            if (file_exists(Mage::getBaseDir('base') . '/lib/ShopymindClient/Bin/Configuration.php')) {
                // Récupération d'un instance et renseignement des paramètres
                $config = ShopymindClient_Bin_Configuration::factory($_POST ['SPMForm'] ['apiIdentification'], $_POST ['SPMForm'] ['apiPassword'], $_POST ['SPMForm'] ['defaultLang'], $_POST ['SPMForm'] ['defaultCurrency'], $_POST ['SPMForm'] ['contactPage'], $_POST ['SPMForm'] ['phoneNumber'], $_POST ['SPMForm'] ['timezone']);
                // Test de la connection au server SPM
                if (! $config->testConnection())
                    Mage::throwException($this->__('Error when test connection'));
                else {
                    // Connexion au serveur et sauvegarde des informations
                    $connect = $config->connectServer();
                    if ($connect !== true) {
                        Mage::throwException($this->__('Error when connect to server'));
                    } else {
                        $message = $this->__('Your form has been submitted successfully.');
                        Mage::getSingleton('adminhtml/session')->addSuccess($message);
                    }
                }
            }
        } catch ( Exception $e ) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*');
    }
}
