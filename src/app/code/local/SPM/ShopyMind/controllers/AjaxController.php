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