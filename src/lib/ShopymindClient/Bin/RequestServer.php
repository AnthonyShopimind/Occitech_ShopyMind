<?php
/**
 * RequestServer
 *
 * @package     ShopymindClient
 * @copyright   Copyright (c) 2013 - IDVIVE SARL (http://www.idvive.com)
 * @license     New BSD license (http://license.idvive.com)
 * @author      Yannick Dalbin <yannick@idvive.com>
 * @version     $Id RequestServer.php 2013-05-21$
 */
if (!isset($SHOPYMIND_CLIENT_CONFIGURATION)) {
    global $SHOPYMIND_CLIENT_CONFIGURATION;
    $SHOPYMIND_CLIENT_CONFIGURATION = array_merge_recursive(
        require dirname(__FILE__) . '/../Src/definitions.php',
        require dirname(__FILE__) . '/../configuration.php'
    );
}

class ShopymindClient_Bin_RequestServer {

    private $_client;

    private $_response = array('success' => false);

    private $_typeRequest = 'GET';

    public function __construct() {
        include_once dirname(__FILE__) . '/../Src/Client.php';
        $this->_client = new ShopymindClient_Src_Client;
    }

    /**
     * Permet d'ajouter des paramètres à la requête
     *
     * @param string $name
     * @param string $value
     * @param string $method
     * @return ShopymindClient_Bin_RequestServer
     */
    public function addParam($name, $value, $method = 'POST') {
        switch (strtolower($method)) {
            case 'get' :
                $this->_client->setParameterGet($name, $value);
                break;
            case 'post' :
                $this->_client->setParameterPost($name, $value);
                $this->_typeRequest = 'POST';
                break;
        }

        return $this;
    }

    /**
     * Permet de parémétrer le service que l'on souhaite interroger
     *
     * @param string $restService
     * @return ShopymindClient_Bin_RequestServer
     */
    public function setRestService($restService) {
        $this->_client->setRestService($restService);
        return $this;
    }

    /**
     * Permet d'éxecuter la requête
     *
     * @return void
     */
    public function send() {
        $response = $this->_client->sendRequest($this->_typeRequest);
		if($response !== null)
        	$this->setResponse($response);

        return (is_array($response) && isset($response['success']) && $response['success'] === true);
    }

    /**
     * Permet de récupérer la réponse du serveur
     *
     * @return array
     */
    public function getResponse() {
        return $this->_response;
    }

    /**
     * Permet de modifier la réponse du serveur
     *
     * @param array $response
     * @return ShopymindClient_Bin_RequestServer
     */
    public function setResponse(array $response) {
        $this->_response = $response;
        return $this;
    }

}
