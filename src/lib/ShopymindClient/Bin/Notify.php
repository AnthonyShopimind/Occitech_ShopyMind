<?php
/**
 * Notify
 *
 * @package     ShopymindClient
 * @copyright   Copyright (c) 2013 - IDVIVE SARL (http://www.idvive.com)
 * @license     New BSD license (http://license.idvive.com)
 * @author      Yannick Dalbin <yannick@idvive.com>
 * @version     $Id Notify.php 2013-05-21$
 */
if (!isset($SHOPYMIND_CLIENT_CONFIGURATION)) {
    global $SHOPYMIND_CLIENT_CONFIGURATION;
    $SHOPYMIND_CLIENT_CONFIGURATION = array_merge_recursive(
        require dirname(__FILE__) . '/../Src/definitions.php',
        require dirname(__FILE__) . '/../configuration.php'
    );
}

require_once dirname(__FILE__) . '/RequestServer.php';

class ShopymindClient_Bin_Notify {

    /**
     * Permet de notifier le serveur qu'un nouveau panier suite à une relance a été créé
     *
     * @param array $params = array('idRemindersSend' => '', 'idCart' => '', 'date' => '')
     * @return boolean
     */
    public static function newCart(array $params) {
        if (!isset($params['date'])) {
            $params['date'] = date('Y-m-d H:i:s');
        }

        $requestServer = new ShopymindClient_Bin_RequestServer;
        $requestServer->setRestService('newcart');

        foreach ($params as $key => $value){
            $requestServer->addParam($key, $value);
        }

        return $requestServer->send();
    }

    /**
     * Permet de notifier le serveur qu'une nouvelle commande a eu lieu
     *
     * @param array $params = array(
     *      'idRemindersSend' => '',
     *      'idCart' => '',
     *      'idOrder' => '',
     *      'products' => array,
     *      'amount' => '',
     *      'currency' => '',
     *      'voucherUsed' => array,
     *      'dateOrder' => ''
     * )
     * @return boolean
     */
    public static function newOrder(array $params) {
        if (!isset($params['date_order'])) {
            $params['date_order'] = date('Y-m-d H:i:s');
        }

        $requestServer = new ShopymindClient_Bin_RequestServer;
        $requestServer->setRestService('neworder');

        foreach ($params as $key => $value){
            $requestServer->addParam($key, $value);
        }

        if($requestServer->send() === true)
        	return $requestServer->getResponse();
        return false;
    }

    /**
     * Permet d'ordonner la synchroniser d'une commande
     *
     * @param array $params
     * @return boolean
     */
    public static function saveOrder($id_order) {
        $requestServer = new ShopymindClient_Bin_RequestServer;
        $requestServer->setRestService('saveorder');
        $requestServer->addParam('order', array($id_order));
        if($requestServer->send() === true)
            return $requestServer->getResponse();
        return false;
    }

    /**
     * Permet de synchroniser un client
     *
     * @param array $params
     * @return boolean
     */
    public static function saveCustomer($id_customer) {
        $requestServer = new ShopymindClient_Bin_RequestServer;
        $requestServer->setRestService('savecustomer');
		$requestServer->addParam('customer', array($id_customer));
        if($requestServer->send() === true)
        	return $requestServer->getResponse();
        return false;
    }

    /**
     * Permet de synchroniser un produit
     *
     * @param array $params
     * @return boolean
     */
    public static function saveProduct($id_product) {
        $requestServer = new ShopymindClient_Bin_RequestServer;
        $requestServer->setRestService('saveproduct');
        $requestServer->addParam('product', array($id_product));
        if($requestServer->send() === true)
            return $requestServer->getResponse();
        return false;
    }

    /**
     * Permet de synchroniser une categorie de produit
     *
     * @param array $params
     * @return boolean
     */
    public static function saveProductCategory($id_category) {
        $requestServer = new ShopymindClient_Bin_RequestServer;
        $requestServer->setRestService('saveproductcategory');
        $requestServer->addParam('productcategory', array($id_category));
        if($requestServer->send() === true)
            return $requestServer->getResponse();
        return false;
    }

    /**
     * Permet de supprimer un client
     *
     * @param array $params
     * @return boolean
     */
    public static function deleteCustomer(array $params) {
        $requestServer = new ShopymindClient_Bin_RequestServer;
        $requestServer->setRestService('deletecustomer');
        $requestServer->addParam('customer', array($params));
        if($requestServer->send() === true)
            return $requestServer->getResponse();
        return false;
    }

    /**
     * Permet de supprimer un groupe de client
     *
     * @param array $params
     * @return boolean
     */
    public static function deleteCustomerGroup(array $params) {
        $requestServer = new ShopymindClient_Bin_RequestServer;
        $requestServer->setRestService('deletecustomergroup');
        $requestServer->addParam('group', array($params));
        if($requestServer->send() === true)
            return $requestServer->getResponse();
        return false;
    }

    /**
     * Permet de supprimer un produit
     *
     * @param array $params
     * @return boolean
     */
    public static function deleteProduct(array $params) {
        $requestServer = new ShopymindClient_Bin_RequestServer;
        $requestServer->setRestService('deleteproduct');
        $requestServer->addParam('product', array($params));
        if($requestServer->send() === true)
            return $requestServer->getResponse();
        return false;
    }

    /**
     * Permet de supprimer une category de produit
     *
     * @param array $params
     * @return boolean
     */
    public static function deleteProductCategory(array $params) {
        $requestServer = new ShopymindClient_Bin_RequestServer;
        $requestServer->setRestService('deleteproductcategory');
        $requestServer->addParam('productcategory', array($params));
        if($requestServer->send() === true)
            return $requestServer->getResponse();
        return false;
    }
}
