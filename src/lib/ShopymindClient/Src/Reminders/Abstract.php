<?php
/**
 * AbstractRelaunch
 *
 * @package     ShopymindClient_Src_Reminders
 * @copyright   Copyright (c) 2013 - IDVIVE SARL (http://www.idvive.com)
 * @license     New BSD license (http://license.idvive.com)
 * @author      Yannick Dalbin <yannick@idvive.com>
 * @version     $Id Abstract.php 2013-04-23$
 */

if (!isset($SHOPYMIND_CLIENT_CONFIGURATION)) {
    global $SHOPYMIND_CLIENT_CONFIGURATION;
    $SHOPYMIND_CLIENT_CONFIGURATION = array_merge_recursive(
        require dirname(__FILE__) . '/../definitions.php',
        require dirname(__FILE__) . '/../../configuration.php'
    );
}

abstract class ShopymindClient_Src_Reminders_Abstract {

    /**
     * Permet de connaitre la liste des emails à exclure
     *
     * @var boolean
     */
    protected $_alreadySend = false;

    /**
     * Permet de compter le nombre de clients affectés par une campagne
     *
     * @var boolean
     */
    protected $_justCount = false;

    /**
     * Permet de notifier le serveur
     *
     * @return boolean
     */
    public function notify() {

    }

    /**
     * Permet la construction de l'objet
     *
     * @param array $params
     * @return ShopymindClient_Src_Reminders_Abstract
     */
    abstract public static function factory(array $params);

    /**
     * Permet de récupérer les infos
     *
     * @return array
     */
    abstract public function get();

    /**
     * Permet de renseigner des options spécifique pour les relances
     *
     * @param array $params
     * @return void
     */
    public function setGenericOptions(array $params) {
        foreach ($params as $nameParam => $value) {
            $method = 'set' . ucfirst($nameParam);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

	/**
     * Permet de récupérer la liste des emails a exclure
     *
     * @return boolean
     */
    public function getAlreadySend() {
        return $this->_alreadySend;
    }

	/**
     * Permet de modifier la liste des emails a exclure
     *
     * @param boolean $generateVoucher
     * @return ShopymindClient_Src_Reminders_Abstract
     */
    public function setAlreadySend($alreadySend) {
        if (!is_array($alreadySend) && !is_bool($alreadySend)) {
            $this->_alreadySend = false;
        }
        else {
            $this->_alreadySend = $alreadySend;
        }

        return $this;
    }

	/**
     * Permet de récupérer la variable justCount
     *
     * @return boolean
     */
    public function getJustCount() {
        return $this->_justCount;
    }

	/**
     * Permet de modifier la variable justCount
     *
     * @param boolean $generateVoucher
     * @return ShopymindClient_Src_Reminders_Abstract
     */
    public function setJustCount($justCount) {
        if (!$justCount) {
            $this->_justCount = false;
        }
        else {
            $this->_justCount = true;
        }

        return $this;
    }

}
