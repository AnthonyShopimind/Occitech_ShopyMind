<?php
/**
 * GoodClientsByAmount
 *
 * @package     ShopymindClient_Src_Reminders
 * @copyright   Copyright (c) 2013 - IDVIVE SARL (http://www.idvive.com)
 * @license     New BSD license (http://license.idvive.com)
 * @author      Yannick Dalbin <yannick@idvive.com>
 * @version     $Id GoodClientsByAmount.php 2013-07-19$
 */
require_once dirname(__FILE__) . '/Abstract.php';

class ShopymindClient_Src_Reminders_GoodClientsByAmount extends ShopymindClient_Src_Reminders_Abstract {

    /**
     * Le montant minimum de commande qui correspond au critère
     *
     * @var double
     */
    protected $_amount;

    /**
     * Le montant maximum de commande qui correspond au critère
     *
     * @var double
     */
    protected $_amountMax;


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

        if (!isset($params['amount'])) {
            return "amount param missing";
        }

        if (!isset($params['nbDays'])) {
            return 'nbDays param missing';
        }
    	if (!isset($params['nbDaysLastOrder'])) {
            return 'nbDaysLastOrder param missing';
        }

        if (!isset($params['dateReference'])) {
            return 'dateReference param missing';
        }

        $relaunch = new self;
        $relaunch->setGenericOptions($params);

        return $relaunch;
    }

    /**
     * Permet d'obtenir le montant minimum
     *
     * @return double
     */
    public function getAmount() {
        return $this->_amount;
    }

    /**
     * Permet de modifier le montant minimum
     *
     * @param double $amount
     * @return ShopymindClient_Src_Reminders_GoodClientsByAmount
     */
    public function setAmount($amount) {
        $this->_amount = $amount;
        return $this;
    }

	/**
     * Permet d'obtenir le montant maximum
     *
     * @return double
     */
    public function getAmountMax() {
        return $this->_amountMax;
    }

    /**
     * Permet de modifier le montant maximum
     *
     * @param double $amountMax
     * @return ShopymindClient_Src_Reminders_GoodClientsByAmount
     */
    public function setAmountMax($amountMax) {
        $this->_amountMax = $amountMax;
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
        if (method_exists('ShopymindClient_Callback', 'getGoodClientsByAmount')) {
            return ShopymindClient_Callback::getGoodClientsByAmount($this->getDateReference(), $this->getTimezones(), $this->getAmount(), $this->getAmountMax(), $this->getNbDays(), $this->getNbDaysLastOrder(), $this->getJustCount());
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
     * Permet de récupérer la liste des fuseaux horaires concernés
     *
     * @return string
     */
    public function getTimezones() {
        return $this->_timezones;
    }

    /**
     * Permet de modifier la liste des fuseaux horaires conserné
     *
     * @param array $timezones
     * @return ShopymindClient_Src_Reminders_BirthdayClients
     */
    public function setTimezones($timezones) {
        $this->_timezones = $timezones;
        return $this;
    }

}
