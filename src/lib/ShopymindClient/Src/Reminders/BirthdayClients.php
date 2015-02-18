<?php
/**
 * BirthdayClients
 *
 * @package     ShopymindClient_Src_Reminders
 * @copyright   Copyright (c) 2013 - IDVIVE SARL (http://www.idvive.com)
 * @license     New BSD license (http://license.idvive.com)
 * @author      Yannick Dalbin <yannick@idvive.com>
 * @version     $Id BirthdayClients.php 2013-04-24$
 */
require_once dirname(__FILE__) . '/Abstract.php';

class ShopymindClient_Src_Reminders_BirthdayClients extends ShopymindClient_Src_Reminders_Abstract {

    protected $_nbDays;

    protected $_dateReference;

    protected $_timezones;

    /**
     * Permet de construire une relance courante
     *
     * @return ShopymindClient_Src_Reminders_BirthdayClients
     */
    public static function factory(array $params) {
        if (!isset($params['timezones'])) {
            return 'timezones param missing';
        }

        if (!isset($params['dateReference'])) {
            return 'dateReference param missing';
        }

        $relaunch = new self;
        $relaunch->setGenericOptions($params);

        return $relaunch;
    }

    /**
     * Permet de récupérer les données de la BDD
     *
     * @return array
     */
    public function get() {
        require_once dirname(__FILE__) . '/../../Callback.php';
        if (method_exists('ShopymindClient_Callback', 'getBirthdayClients')) {
            return ShopymindClient_Callback::getBirthdayClients($this->getShopIdShop(), $this->getDateReference(), $this->getTimezones(), $this->getNbDays(), $this->getJustCount());
        }

        return null;
    }

    /**
     * Permet d'obtenir le nombre de jour avant la date d'anniv
     *
     * @return int
     */
    public function getNbDays() {
        return $this->_nbDays;
    }

    /**
     * Permet de modifier le nombre de jour avant la date d'anniv
     *
     * @param int $nbDays
     * @return ShopymindClient_Src_Reminders_BirthdayClients
     */
    public function setNbDays($nbDays) {
        $this->_nbDays = $nbDays;
        return $this;
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
     * Permet de récupérer le timezone concerné
     *
     * @return array
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
    public function setTimezones(array $timezones) {
        $this->_timezones = $timezones;
        return $this;
    }

}
