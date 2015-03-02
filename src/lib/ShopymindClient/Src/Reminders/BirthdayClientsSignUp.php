<?php
/**
 * BirthdayClients
 *
 * @package     ShopymindClient_Reminders
 * @copyright   Copyright (c) 2013 - IDVIVE SARL (http://www.idvive.com)
 * @license     New BSD license (http://license.idvive.com)
 * @author      Yannick Dalbin <yannick@idvive.com>
 * @version     $Id BirthdayClients.php 2013-04-24$
 */
require_once dirname(__FILE__) . '/Abstract.php';

class ShopymindClient_Src_Reminders_BirthdayClientsSignUp extends ShopymindClient_Src_Reminders_Abstract {

    protected $_dateReference;

    protected $_timezones;

    /**
     * Permet de construire une relance courante
     *
     * @return ShopymindClient_Src_Reminders_BirthdayClientsSignUp
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
            return ShopymindClient_Callback::getBirthdayClientsSignUp($this->getShopIdShop(), $this->getDateReference(), $this->getTimezones(), $this->getJustCount());
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
     * @return ShopymindClient_Src_Reminders_BirthdayClientsSignUp
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
     * @return ShopymindClient_Src_Reminders_BirthdayClientsSignUp
     */
    public function setTimezones(array $timezones) {
        $this->_timezones = $timezones;
        return $this;
    }

}
