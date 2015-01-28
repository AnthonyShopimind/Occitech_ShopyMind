<?php
/**
 * Server
 * 
 * @package     ShopymindClient
 * @copyright   Copyright (c) 2013 - IDVIVE SARL (http://www.idvive.com)
 * @license     New BSD license (http://license.idvive.com)
 * @author      Yannick Dalbin <yannick@idvive.com>
 * @version     $Id Server.php 2013-04-23$
 */

require_once dirname(__FILE__) . '/library/SZend/Controller/Request/Http.php';
require_once dirname(__FILE__) . '/library/SZend/Json.php';

if (!isset($SHOPYMIND_CLIENT_CONFIGURATION)) {
    global $SHOPYMIND_CLIENT_CONFIGURATION;
    $SHOPYMIND_CLIENT_CONFIGURATION = array_merge_recursive(
        require dirname(__FILE__) . '/definitions.php',
        require dirname(__FILE__) . '/../configuration.php'
    );
}

final class ShopymindClient_Src_Server {

    /** 
     * Requête http
     *
     * @var SZend_Controller_Request_Http
     */
    private $_request;
     
    public function __construct() {
        $this->_initRequest();
    }

    private function _initRequest() {
        $this->setRequest(new SZend_Controller_Request_Http);
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

    public function isValid($data = null) {
        global $SHOPYMIND_CLIENT_CONFIGURATION;
        $config = $SHOPYMIND_CLIENT_CONFIGURATION;

        $client_id = $this->getRequest()->getHeader($config['header']['client_id']);
        $request_hmac = $this->getRequest()->getHeader($config['header']['hmac']);

        if ($data === null) {
            if ($this->getRequest()->isPost()) {
                $data = $this->getRequest()->getPost();
            }
            elseif ($this->getRequest()->isGet()) {
                $data = $this->getRequest()->getQuery();
            }
        }

        if (is_array($data)) {
            uksort($data, 'strnatcasecmp');
            $data = $this->implode_recursive($data);
        }

        $hmac = hash_hmac('sha256', $data, sha1($config['api']['password']));

        return (md5($hmac) === md5($request_hmac) && md5($config['api']['identifiant']) === md5($client_id));
    }

    
    /**
     * Permet de renseigner une request
     *
     * @param SZend_Controller_Request_Http $request
     * @return ShopymindClient_Src_Server
     */
    public function setRequest($request) {
        $this->_request = $request;
        return $this;
    }
    
    /**
     * Permet de récupérer la requête 
     *
     * @return SZend_Controller_Request_Http
     */
    public function getRequest() {
        return $this->_request;
    }

    /**
     * Permet de retrouver la relance concernée par la requête
     * 
     * @return string
     */
    public function retrieveRelaunch() {
        global $SHOPYMIND_CLIENT_CONFIGURATION;
        $config = $SHOPYMIND_CLIENT_CONFIGURATION;
        if ($this->getRequest()->isPost()) {
            $relaunch = $this->getRequest()->getPost($config['get']['relaunch']);
        }
        elseif ($this->getRequest()->isGet()) {
            $relaunch = $this->getRequest()->getQuery($config['get']['relaunch']);
        }
        if ($relaunch !== null) {
            return str_replace(' ', '', ucwords(str_replace('-', ' ', $relaunch)));
        }

        return null;
    }

    /**
     * Permet de retrouver les paramètres de relance
     * 
     * @return array
     */
    public function retrieveParams() {
        global $SHOPYMIND_CLIENT_CONFIGURATION;
        $config = $SHOPYMIND_CLIENT_CONFIGURATION;

        $getDefined = $config['get'];
        $params = array();
        if ($this->getRequest()->isPost()) {
            $paramsReceive = $this->getRequest()->getPost();
        }
        elseif ($this->getRequest()->isGet()) {
            $paramsReceive = $this->getRequest()->getQuery();
        }
        foreach ($paramsReceive as $name => $val) {
            if (!in_array($name, $params)) {
                $params[$name] = $val;
            }
        }

        return $params;
    }

    /**
     * Permet de récupérer le type de requête que l'on souhaite executer
     * 
     * @return string
     */
    public function getTypeRequest() {
        global $SHOPYMIND_CLIENT_CONFIGURATION;
        $config = $SHOPYMIND_CLIENT_CONFIGURATION;
        return $this->getRequest()->getHeader($config['get']['type-request']);
    }

    /**
     * Permet d'envoyer une réponse
     * 
     * @param array|string $data
     * @return string
     */
    public function sendResponse(array $data, $success = true) {
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-type: application/json');
        
        if (!isset($data['success'])) {
            $data['success'] = $success;
        }

        echo SZend_Json::encode($data);
        exit;
    }

}
