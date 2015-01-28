<?php
/**
 * MissingClients
 *
 * @package     ShopymindClient_Src_Reminders
 * @copyright   Copyright (c) 2013 - IDVIVE SARL (http://www.idvive.com)
 * @license     New BSD license (http://license.idvive.com)
 * @author      Yannick Dalbin <yannick@idvive.com>
 * @version     $Id MissingClients.php 2013-04-24$
 */
require_once dirname(__FILE__) . '/Abstract.php';

class ShopymindClient_Src_Reminders_MissingClients extends ShopymindClient_Src_Reminders_Abstract {

    /**
     * Nombre de jours sans commandes
     * O = jamais de commandes
     *
     * @var int
     */
    protected $_nbDays;

    protected $_dateReference;

    protected $_timezones;

    protected $_relaunchOlder = false;

    /**
     * Permet de construire une relance courante
     *
     * @return ShopymindClient_Src_Reminders_MissingClients
     */
    public static function factory(array $params) {
        if (!isset($params['timezones'])) {
            return 'timezones param missing';
        }

        if (!isset($params['dateReference'])) {
            return 'dateReference param missing';
        }

        if (!isset($params['nbDays'])) {
            return 'nbDays param missing';
        }

        $relaunch = new self;
        $relaunch->setGenericOptions($params);

        return $relaunch;
    }

    /**
     * Permet de récupérer le nombre de jours sans commandes
     *
     * @return int
     */
    public function getNbDays() {
        return $this->_nbDays;
    }

    /**
     * Permet de modifier le nombre de jours dans commandes
     *
     * @param int $nbDays
     * @return ShopymindClient_Src_Reminders_MissingClients
     */
    public function setNbDays($nbDays) {
        $this->_nbDays = (int) $nbDays;
        return $this;
    }

	/**
     * Permet de savoir si l'on doit relancer les clients plus anciens que _nbDays
     *
     * @return bool
     */
    public function getRelaunchOlder() {
        return (bool)$this->_relaunchOlder;
    }

    /**
     * Permet de modifier la variable _relaunchOlder
     *
     * @param bool $relaunchOlder
     * @return ShopymindClient_Src_Reminders_MissingClients
     */
    public function setRelaunchOlder($relaunchOlder) {
        $this->_relaunchOlder = (bool) $relaunchOlder;
        return $this;
    }

    /**
     * Permet de récupérer les données de la BDD
     *
     * @return array
     */
    public function get() {
        require_once dirname(__FILE__) . '/../../Callback.php';
        if (method_exists('ShopymindClient_Callback', 'getMissingClients')) {
            return ShopymindClient_Callback::getMissingClients($this->getDateReference(), $this->getTimezones(), $this->getNbDays(), $this->getRelaunchOlder(), $this->getJustCount());
        }

        return null;
    }

    /**
     * Permet de récupérer la date de référence à prendre en compte
     *
     * @return string
     */
    public function getDateReference() {
        return $this->_dateReference;
    }

    /**
     * Permet de modifier la date de référence à prendre en compte
     *
     * @param string $dateReference
     * @return ShopymindClient_Src_Reminders_BirthdayClients
     */
    public function setDateReference($dateReference) {
        $this->_dateReference = $dateReference;
        return $this;
    }

    /**
     * Permet de récupérer la liste des timezones
     *
     * @return string
     */
    public function getTimezones() {
        return $this->_timezones;
    }

    /**
     * Permet de modifier la liste des timezones concernés
     *
     * @param array $timezones
     * @return ShopymindClient_Src_Reminders_
     */
    public function setTimezones(array $timezones) {
        $this->_timezones = $timezones;
        return $this;
    }

}
