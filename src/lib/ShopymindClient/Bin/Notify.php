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
        if (!isset($params['dateOrder'])) {
            $params['dateOrder'] = date('Y-m-d H:i:s');
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
     * Permet de notifier le serveur d'un nouveau client OPT IN
     *
     * @param array $params = array(
     *      'last_name' => '',
     *      'first_name' => '',
     *      'email_address' => '',
     *      'birthday' => 'Y-m-d',
     *      'locale' => 'lang_COUNTRY',
     *      'gender' => '1 = mâle, 2 => femme',
     *      'ip' => ''
     * )
     * @return string|bool
     */
    public static function addOptInClient(array $params) {
        $requestServer = new ShopymindClient_Bin_RequestServer;
        $requestServer->setRestService('addoptinclient');

        foreach ($params as $key => $value){
            $requestServer->addParam($key, $value);
        }

        if ($requestServer->send() === true) {
            $response = $requestServer->getResponse();
            if (isset($response['id'])) {
                return $response['id'];
            }
        }

        return false;
    }
    
    /**
     * Permet de notifier le serveur d'une nouvelle inscription
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
    public static function newCustomer(array $params) {
        $requestServer = new ShopymindClient_Bin_RequestServer;
        $requestServer->setRestService('newcustomer');
    
        foreach ($params as $key => $value){
            $requestServer->addParam($key, $value);
        }
    
        if($requestServer->send() === true)
            return $requestServer->getResponse();
        return false;
    }

}
