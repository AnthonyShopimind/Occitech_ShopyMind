<?php
/**
 * DroppedOutCart
 *
 * @package     ShopymindClient_Src_Reminders
 * @copyright   Copyright (c) 2013 - IDVIVE SARL (http://www.idvive.com)
 * @license     New BSD license (http://license.idvive.com)
 * @author      Yannick Dalbin <yannick@idvive.com>
 * @version     $Id DroppedOutCart.php 2013-04-23$
 */
require_once dirname(__FILE__) . '/Abstract.php';

class ShopymindClient_Src_Reminders_DroppedOutCart extends ShopymindClient_Src_Reminders_Abstract {

    /**
     * Nombre d'heure d'innactivité de panier
     *
     * @var int
     */
    protected $_intervalAction;
    
    /**
     * Nombre de secondes maximum d'innactivité de panier
     *
     * @var int
     */
    protected $_maxIntervalAction;

    /**
     * Permet de construire une relance courante
     *
     * @return ShopymindClient_Src_Reminders_DroppedOutCart
     */
    public static function factory(array $params) {
        if (!isset($params['intervalAction'])) {
            return 'intervalAction param missing';
        }

        $relaunch = new self;
        $relaunch->setGenericOptions($params);

        return $relaunch;
    }

    /**
     * Permet de récupérer le nombre d'heure de panier innactif
     *
     * @return int
     */
    public function getNbHours() {
        return $this->_nbHours;
    }

    /**
     * Permet de modifier le nombre d'heure d'inactivité de panier
     *
     * @param int $nbHours
     * @return ShopymindClient_Src_Reminders_DroppedOutCart
     */
    public function setNbHours($nbHours) {
        $this->_nbHours = (int) $nbHours;
        return $this;
    }

    /**
     * Permet de récupérer les données de la BDD
     *
     * @return array
     */
    public function get() {
        require_once dirname(__FILE__) . '/../../Callback.php';
        if (method_exists('ShopymindClient_Callback', 'getDroppedOutCart')) {
            return ShopymindClient_Callback::getDroppedOutCart($this->getShopIdShop(),$this->getIntervalAction(),$this->getMaxIntervalAction(), $this->getJustCount());
        }

        return null;
    }

    /**
     * Permet d'obtenir le nombre d'heure d'inactivité de panier
     *
     * @return int
     */
    public function getIntervalAction() {
        return $this->_intervalAction;
    }

    /**
     * Permet de modifier le nombre d'heure d'inactivité de panier
     *
     * @param int $intervalAction
     * @return ShopymindClient_Src_Reminders_DroppedOutCart
     */
    public function setIntervalAction($intervalAction) {
        $this->_intervalAction = $intervalAction;
        return $this;
    }
    
    /**
     * Permet d'obtenir le nombre de seconde maximum d'inactivité de panier
     *
     * @return int
     */
    public function getMaxIntervalAction() {
        return $this->_maxIntervalAction;
    }
    
    /**
     * Permet de modifier de seconde maximum d'inactivité de panier
     *
     * @param int $intervalAction
     * @return ShopymindClient_Src_Reminders_DroppedOutCart
     */
    public function setMaxIntervalAction($maxIntervalAction) {
        $this->_maxIntervalAction = $maxIntervalAction;
        return $this;
    }
}
