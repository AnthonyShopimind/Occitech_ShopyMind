<?php
/**
 * Client
 *
 * @package     ShopymindClient
 * @copyright   Copyright (c) 2013 - IDVIVE SARL (http://www.idvive.com)
 * @license     New BSD license (http://license.idvive.com)
 * @author      Yannick Dalbin <yannick@idvive.com>
 * @version     $Id Client.php 2013-04-24$
 */
require_once dirname(__FILE__) . '/library/SZend/Http/Client.php';
require_once dirname(__FILE__) . '/library/SZend/Json.php';

if (!isset($SHOPYMIND_CLIENT_CONFIGURATION)) {
    global $SHOPYMIND_CLIENT_CONFIGURATION;
    $SHOPYMIND_CLIENT_CONFIGURATION = array_merge_recursive(
        require dirname(__FILE__) . '/definitions.php',
        require dirname(__FILE__) . '/../configuration.php'
    );
}

final class ShopymindClient_Src_Client {

    protected $_reminders = array(
        'getDroppedOutCart'             => 1,
        'getBirthdayClients'            => 2,
        'getGoodClientsByAmount'        => 4,
        'getGoodClientsByNumberOrders'  => 8,
        'getMissingClients'             => 16,
        'getOrdersByStatus'             => 32,
        'getVoucherUnused'              => 64,
        'sendOrderToSPM'              	 => 128,
        'getBirthdayClientsSignUp'      => 256,
        'getInactiveClients'      		    => 512,
    );

    /**
     * Permet d'envoyer des requêtes au serveur
     *
     * @var SZend_Http_Client
     */
    private $_client;

    private $_restService;

    public function __construct() {
        global $SHOPYMIND_CLIENT_CONFIGURATION;
        $config = $SHOPYMIND_CLIENT_CONFIGURATION;

        $url = $config['api']['url'];

        $this->setClient(new SZend_Http_Client($url));
        $this->initHeaders();
    }

    /**
     * Permet d'initialiser le header avec des valeurs par défaut
     *
     * @return void
     */
    public function initHeaders() {
        global $SHOPYMIND_CLIENT_CONFIGURATION;
        $config = $SHOPYMIND_CLIENT_CONFIGURATION;

        $this->getClient()->setHeaders($config['header']['client_id'], $config['api']['identifiant'])
                          ->setHeaders($config['header']['build'], $this->getBuild())
                          ->setHeaders($config['header']['version'], $config['version'])
                          ->setHeaders('version', $config['version']);
    }
    protected function formatString($value, $key, $options) {
        if (!empty($value)) {
            $options['glued_string'] .= $key.$options['glue'];
            $options['glued_string'] .= $value.$options['glue'];
        }
    }
    private function implode_recursive(array $array, $glue = ';') {
        $glued_string = '';
        // Recursively iterates array and adds key/value to glued string
        array_walk_recursive($array, array($this, 'formatString'), array('glue' => $glue, 'glued_string' => &$glued_string));
        // Removes last $glue from string
        strlen($glue) > 0 and $glued_string = substr($glued_string, 0, -strlen($glue));

        // Trim ALL whitespace
        $glued_string = preg_replace("/(\s)/ixsm", '', $glued_string);

        return (string) $glued_string;
    }


    /**
     * Permet de récupérer le token
     *
     * @return string
     */
    public function getToken() {
        global $SHOPYMIND_CLIENT_CONFIGURATION;
        $config = $SHOPYMIND_CLIENT_CONFIGURATION;

        $params = $this->getClient()->getParams();

        if ($params !== null) {
            uksort($params, 'strnatcasecmp');
            $params = $this->implode_recursive($params);
        }

        $password = sha1($config['api']['password']);

        return hash_hmac('sha256', $params, $password);
    }

    /**
     * Connecteur HTTP
     *
     * @return SZend_Http_Client
     */
    public function getClient() {
        return $this->_client;
    }

    /**
     * Permet de modifier le connecteur HTTP
     *
     * @param SZend_Http_Client $client
     * @return ShopymindClient_Src_Client
     */
    public function setClient($client) {
        $this->_client = $client;
        return $this;
    }

    /**
     * Permet d'ajouter des paramètres post à la requête
     *
     * @return ShopymindClient_Src_Client
     */
    public function setParameterPost($name, $value = null) {
        $this->getClient()->setParameterPost($name, $value);
        return $this;
    }

    /**
     * Permet d'ajouter des paramètres get à la requête
     *
     * @return ShopymindClient_Src_Client
     */
    public function setParameterGet($name, $value = null) {
        $this->getClient()->setParameterGet($name, $value);
        return $this;
    }

    /**
     * Permet d'envoyer la requête
     *
     * @return SZend_Http_Response
     */
    public function sendRequest($method = 'GET') {
        global $SHOPYMIND_CLIENT_CONFIGURATION;
        $config = $SHOPYMIND_CLIENT_CONFIGURATION;

        //$this->getClient()->setAdapter('SZend_Http_Client_Adapter_Curl');
        $this->getClient()->setMethod($method);

        $this->getClient()->setConfig(array(
                'timeout'      => 5
            )
        );
        $token = $this->getToken();
        $this->getClient()->setHeaders($config['header']['hmac'], $token);

        if ($this->getRestService() !== null) {
            $uri = $this->getClient()->getUri(true);
            $uri .= '/' . $this->getRestService();
            $this->getClient()->setUri($uri);
        }

        $response = $this->getClient()->request();
        return SZend_Json::decode($response->getBody());
    }

    /**
     * Permet de tester la bonne communication avec le serveur
     *
     * @return boolean
     */
    public function sayHello() {
        $this->setParameterPost('testCommunication', time());
        $response = $this->sendRequest('POST');

        if (is_array($response) && isset($response['success'])) {
            return $response['success'];
        }

        return false;
    }

    /**
     * Permet de récupérer la build du client
     *
     * @return int
     */
    public function getBuild() {
        $build = 0;

        if (file_exists(dirname(__FILE__) . '/../Callback.php')) {
            require_once dirname(__FILE__) . '/../Callback.php';

            foreach (get_class_methods('ShopymindClient_Callback') as $method) {
                if (array_key_exists($method, $this->_reminders)) {
                    $build += $this->_reminders[$method];
                }
            }
        }

        return $build;
    }

    /**
     * Permet de récupérer le service à appeler
     *
     * @return string
     */
    public function getRestService() {
        return $this->_restService;
    }

    /**
     * Permet de définir le service à appeler
     *
     * @param string $restService
     * @return ShopymindClient_Src_Client
     */
    public function setRestService($restService) {
        $this->_restService = $restService;
        return $this;
    }

}
