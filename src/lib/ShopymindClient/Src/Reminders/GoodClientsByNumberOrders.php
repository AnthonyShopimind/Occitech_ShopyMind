<?php
/**
 * GoodClientsByNumberOrders
 *
 * @package     ShopymindClient_Src_Reminders
 * @copyright   Copyright (c) 2013 - IDVIVE SARL (http://www.idvive.com)
 * @license     New BSD license (http://license.idvive.com)
 * @author      Yannick Dalbin <yannick@idvive.com>
 * @version     $Id GoodClientsByNumberOrders.php 2013-07-19$
 */
require_once dirname(__FILE__) . '/Abstract.php';

class ShopymindClient_Src_Reminders_GoodClientsByNumberOrders extends ShopymindClient_Src_Reminders_Abstract {

    /**
     * Le nombre total de commande
     *
     * @var int
     */
    protected $_number;

    /**
     * Le nombre maximum de commande
     *
     * @var int
     */
    protected $_numberMax;

    /**
     * Le nombre de jours comptabilisé
     *
     * @var int
     */
    protected $_nbDays;

    /**
     * Le nombre de jours minimum depuis la dernière commande
     *
     * @var int
     */
    protected $_nbDaysLastOrder;

    protected $_dateReference;

    protected $_timezones;

    public static function factory(array $params) {
        if (!isset($params['timezones'])) {
            return "timezones param missing";
        }

        if (!isset($params['dateReference'])) {
            return 'dateReference param missing';
        }

        if (!isset($params['number'])) {
            return "number param missing";
        }

        if (!isset($params['nbDays'])) {
            return 'nbDays param missing';
        }

    	if (!isset($params['nbDaysLastOrder'])) {
            return 'nbDaysLastOrder param missing';
        }

        $relaunch = new self;
        $relaunch->setGenericOptions($params);

        return $relaunch;
    }

    /**
     * Permet d'obtenir le nombre minimum de commande
     *
     * @return double
     */
    public function getNumber() {
        return $this->_number;
    }

    /**
     * Permet de modifier le nombre minimum de commande
     *
     * @param int $number
     * @return ShopymindClient_Src_Reminders_GoodClientsByAmount
     */
    public function setNumber($number) {
        $this->_number = $number;
        return $this;
    }

	/**
     * Permet d'obtenir le nombre maximum de commande
     *
     * @return double
     */
    public function getNumberMax() {
        return $this->_numberMax;
    }

    /**
     * Permet de modifier le nombre maximum de commande
     *
     * @param int $numberMax
     * @return ShopymindClient_Src_Reminders_GoodClientsByAmount
     */
    public function setNumberMax($numberMax) {
        $this->_numberMax = $numberMax;
        return $this;
    }

    /**
     * Permet d'obtenir la devise
     *
     * @return string
     */
    public function getCurrency() {
        return $this->_currency;
    }

    /**
     * Permet de modifier la devise
     *
     * @param string $currency
     * @return ShopymindClient_Src_Reminders_GoodClientsByAmount
     */
    public function setCurrency($currency) {
        $this->_currency = $currency;
        return $this;
    }

    /**
     * Permet d'obtenir le nombre de jour
     *
     * @return int
     */
    public function getNbDays() {
        return $this->_nbDays;
    }

    /**
     * Permet de modifier le nombre de jours
     *
     * @param int $nbDays
     * @return ShopymindClient_Src_Reminders_GoodClientsByAmount
     */
    public function setNbDays($nbDays) {
        $this->_nbDays = $nbDays;
        return $this;
    }

	/**
     * Permet d'obtenir le nombre de jours de la dernière commande
     *
     * @return int
     */
    public function getNbDaysLastOrder() {
        return $this->_nbDaysLastOrder;
    }

    /**
     * Permet de modifier le nombre de jours de la dernière commande
     *
     * @param int $nbDays
     * @return ShopymindClient_Src_Reminders_GoodClientsByAmount
     */
    public function setNbDaysLastOrder($nbDaysLastOrder) {
        $this->_nbDaysLastOrder = $nbDaysLastOrder;
        return $this;
    }

    public function get() {
        require_once dirname(__FILE__) . '/../../Callback.php';
        if (method_exists('ShopymindClient_Callback', 'getGoodClientsByNumberOrders')) {
            return ShopymindClient_Callback::getGoodClientsByNumberOrders($this->getDateReference(), $this->getTimezones(), $this->getNumber(), $this->getNumberMax(), $this->getNbDays(), $this->getNbDaysLastOrder(), $this->getJustCount());
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
     * Permet de récupérer la liste des timezones concernés
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
     * @return ShopymindClient_Src_Reminders_BirthdayClients
     */
    public function setTimezones($timezones) {
        $this->_timezones = $timezones;
        return $this;
    }

}
