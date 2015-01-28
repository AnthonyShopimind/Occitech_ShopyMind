<?php
/**
 * OrdersByStatus
 *
 * @package     ShopymindClient_Src_Reminders
 * @copyright   Copyright (c) 2013 - IDVIVE SARL (http://www.idvive.com)
 * @license     New BSD license (http://license.idvive.com)
 * @author      Yannick Dalbin <yannick@idvive.com>
 * @version     $Id OrdersByStatus.php 2013-04-24$
 */
require_once dirname(__FILE__) . '/Abstract.php';

class ShopymindClient_Src_Reminders_OrdersByStatus extends ShopymindClient_Src_Reminders_Abstract {

    /**
     * Nombre de jours sans paiement finalisé
     *
     * @var int
     */
    protected $_nbDays;

    /**
     * ID du status de la commande concernée
     *
     * 0var mixed
     */
    protected $_idStatus;

    protected $_dateReference;

    protected $_timezones;

    /**
     * Permet de construire une relance courante
     *
     * @return ShopymindClient_Src_Reminders_NotFinalizedPayments
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

        if (!isset($params['idStatus'])) {
            return 'idStatus param missing';
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
     * @return ShopymindClient_Src_Reminders_NotFinalizedPayments
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
        if (method_exists('ShopymindClient_Callback', 'getOrdersStatus')) {
            return ShopymindClient_Callback::getOrdersByStatus($this->getDateReference(), $this->getTimezones(), $this->getNbDays(), $this->getIdStatus(), $this->getJustCount());
        }

        return null;
    }

    /**
     * Permet d'obtenir l'id du status concerné
     *
     * @return mixed
     */
    public function getIdStatus() {
        return $this->_idStatus;
    }

    /**
     * Permet de modifier l'id du status concerné
     *
     * @param mixed $idStatus
     * @return ShopymindClient_Src_Reminders_OrdersByStatus
     */
    public function setIdStatus($idStatus) {
        $this->_idStatus = $idStatus;
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
