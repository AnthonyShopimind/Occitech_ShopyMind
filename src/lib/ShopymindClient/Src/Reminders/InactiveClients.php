<?php
/**
 * InactiveClients
 *
 * @package     ShopymindClient_Reminders
 * @copyright   Copyright (c) 2013 - IDVIVE SARL (http://www.idvive.com)
 * @license     New BSD license (http://license.idvive.com)
 * @author      Yannick Dalbin <yannick@idvive.com>
 * @version     $Id InactiveClients.php 2013-04-24$
 */
require_once dirname(__FILE__) . '/Abstract.php';

class ShopymindClient_Src_Reminders_InactiveClients extends ShopymindClient_Src_Reminders_Abstract {

    protected $_dateReference;

    protected $_timezones;
    /**
     * Le nombre de mois  depuis la dernière commande
     *
     * @var int
     */
    protected $_nbMonthsLastOrder;

    protected $_relaunchOlder = false;

    /**
     * Permet de construire une relance courante
     *
     * @return ShopymindClient_Src_Reminders_InactiveClients
     */
    public static function factory(array $params) {
        if (!isset($params['timezones'])) {
            return 'timezones param missing';
        }

        if (!isset($params['dateReference'])) {
            return 'dateReference param missing';
        }

        if (!isset($params['nbMonthsLastOrder'])) {
            return 'nbMonthsLastOrder param missing';
        }

        $relaunch = new self;
        $relaunch->setGenericOptions($params);

        return $relaunch;
    }

    /**
     * Permet de récupérer le nombre de mois de la dernière commande
     *
     * @return int
     */
    public function getNbMonthsLastOrder() {
        return $this->_nbMonthsLastOrder;
    }

    /**
     * Permet de modifier le nombre de mois de la dernière commande
     *
     * @param int $nbMonthsLastOrder
     * @return ShopymindClient_Src_Reminders_InactiveClients
     */
    public function setNbMonthsLastOrder($nbMonthsLastOrder) {
        $this->_nbMonthsLastOrder = (int) $nbMonthsLastOrder;
        return $this;
    }

	/**
     * Permet de savoir si l'on doit relancer les clients plus anciens
     *
     * @return bool
     */
    public function getRelaunchOlder() {
        return (int)$this->_relaunchOlder;
    }

    /**
     * Permet de modifier la variable _relaunchOlder
     *
     * @param bool $relaunchOlder
     * @return ShopymindClient_Src_Reminders_InactiveClients
     */
    public function setRelaunchOlder($relaunchOlder) {
        $this->_relaunchOlder = (int) $relaunchOlder;
        return $this;
    }

    /**
     * Permet de récupérer les données de la BDD
     *
     * @return array
     */
    public function get() {
        require_once dirname(__FILE__) . '/../../Callback.php';
        if (method_exists('ShopymindClient_Callback', 'getInactiveClients')) {
            return ShopymindClient_Callback::getInactiveClients($this->getShopIdShop(), $this->getDateReference(), $this->getTimezones(), $this->getNbMonthsLastOrder(), $this->getRelaunchOlder(), $this->getJustCount());
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
