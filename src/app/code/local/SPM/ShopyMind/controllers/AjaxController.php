<?php
/**
 * SPM_ShopyMind_AjaxController
 *
 * @package     SPM_ShopyMind
 * @copyright   Copyright (c) 2014 - ShopyMind SARL (http://www.shopymind.com)
 * @license     New BSD license (http://license.shopymind.com)
 * @author      Couvert Jean-Sébastien <js.couvert@shopymind.com>
 * @version     $Id AjaxController.php 2014-12-17$
 */
class SPM_ShopyMind_AjaxController extends Mage_Core_Controller_Front_Action {
    public function indexAction() {
        switch ($this->getRequest()->getParam('action')) {
            case 'frontCheck' :
                try {
                    $json_return = array (
                            'success' => 1
                    );
                    // Check if cart following a reminder
                    ShopymindClient_Callback::checkNewCart();

                    // Get optin client for future retargeting service
                    if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                        $cookie = Mage::app()->getCookie();
                        $email = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
                        $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
                        if ($subscriber->isSubscribed()) {
                            $customer = Mage::helper('customer')->getCustomer()->getData();
                            $params = array (
                                    'last_name' => @$customer ['lastname'],
                                    'first_name' => @$customer ['firstname'],
                                    'email_address' => @$customer ['email'],
                                    'birthday' => @$customer ['dob'],
                                    'locale' => self::getUserLocale($customer ['entity_id'], $customer ['store_id']),
                                    'gender' => @$customer ['gender'],
                                    'ip' => Mage::helper('core/http')->getRemoteAddr()
                            );
                            $signature = sha1(serialize($params));
                            if ($cookie->get('spmoptinchecksum') != '' && $cookie->get('spmoptinchecksum') == $signature) {
                            } else {
                                $optinkey = ShopymindClient_Bin_Notify::addOptInClient($params);
                                if ($optinkey) {
                                    $cookie->set('spmoptinchecksum', $signature, ((3600 * 24) * 365));
                                    $json_return ['optinkey'] = $optinkey;
                                }
                            }
                        }
                    }
                } catch ( Exception $e ) {
                    $json_return ['success'] = 0;
                    $json_return ['error'] = $e->getMessage();
                }
                if (ob_get_level()) {
                    ob_clean();
                }
                echo json_encode($json_return);
                die();
            break;
        }
    }
    /**
     *
     * @param unknown $id_customer
     * @param unknown $store_id
     * @return string unknown
     */
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