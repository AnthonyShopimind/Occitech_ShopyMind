<?php
/**
 * VoucherUnused
 *
 * @package     ShopymindClient_Src_Reminders
 * @copyright   Copyright (c) 2013 - IDVIVE SARL (http://www.idvive.com)
 * @license     New BSD license (http://license.idvive.com)
 * @author      Yannick Dalbin <yannick@idvive.com>
 * @version     $Id VoucherUnused.php 2013-04-24$
 */
require_once dirname(__FILE__) . '/Abstract.php';

class ShopymindClient_Src_Reminders_VoucherUnused extends ShopymindClient_Src_Reminders_Abstract {

    /**
     * Nombre de jours avant expiration
     *
     * @var int
     */
    protected $_nbDays;

    protected $_dateReference;

    protected $_timezones;

    protected $_startBy;

    protected $_excludeSPM;

    /**
     * Permet de construire une relance courante
     *
     * @return ShopymindClient_Src_Reminders_VoucherUnused
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
     * Permet de récupérer l'option d'exclusion des codes SPM
     *
     * @return int
     */
    public function getExcludeSPM() {
        return $this->_excludeSPM;
    }

    /**
     * Permet de modifier l'option d'exclusion des codes SPM
     *
     * @param int $excludeSPM
     * @return ShopymindClient_Src_Reminders_VoucherUnused
     */
    public function setExcludeSPM($excludeSPM) {
        $this->_excludeSPM = (int) $excludeSPM;
        return $this;
    }

	/**
     * Permet de récupérer la conditionnelle du code de réduction
     *
     * @return int
     */
    public function getStartBy() {
        return $this->_startBy;
    }

    /**
     * Permet de modifier la conditionnelle du code de réduction
     *
     * @param int $startBy
     * @return ShopymindClient_Src_Reminders_VoucherUnused
     */
    public function setStartBy($startBy) {
        $this->_startBy = $startBy;
        return $this;
    }

    /**
     * Permet de récupérer le nombre de jours avant expiration du bon de réduction
     *
     * @return int
     */
    public function getNbDays() {
        return $this->_nbDays;
    }

    /**
     * Permet de modifier le nombre de jours avant expiration du bon
     *
     * @param int $nbDayExpiration
     * @return ShopymindClient_Src_Reminders_VoucherUnused
     */
    public function setNbDays($nbDays) {
        $this->_nbDays = (int) $nbDays;
        return $this;
    }

    /**
     * Permet de récupérer les données de la BDD
     *
     * @return array
     */
    public function get() {
        require_once dirname(__FILE__) . '/../../Callback.php';
        if (method_exists('ShopymindClient_Callback', 'getVoucherUnused')) {
            return ShopymindClient_Callback::getVoucherUnused($this->getShopIdShop(),$this->getDateReference(), $this->getTimezones(), $this->getNbDays(), $this->getStartBy(), $this->getJustCount());
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
        $this->_dateReference = substr($dateReference, 0, 10) . ' 00:00:00';
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
     * Permet de modifier la liste des timezones
     *
     * @param array $timezones
     * @return ShopymindClient_Src_Reminders_BirthdayClients
     */
    public function setTimezones(array $timezones) {
        $this->_timezones = $timezones;
        return $this;
    }

}
