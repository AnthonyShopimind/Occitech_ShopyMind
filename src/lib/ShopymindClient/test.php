<?php

/**
 * Example :
 * http://www.yourdomain.com/lib/ShopymindClient/test.php?type=getBirthdayClients&dateReference=2013-10-01
 */
require_once dirname(__FILE__) . '/Callback.php';

error_reporting(E_ALL);
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);

$type = $_GET ['type'];
$justCount = (isset($_GET ['justCount']) ? $_GET ['justCount'] : false);
$shopIdShop = (isset($_GET ['shopIdShop']) ? $_GET ['shopIdShop'] : false);

switch ($type) {

    case 'getBirthdayClients' :
        $dateReference = $_GET ['dateReference'];
        $timezones = (isset($_GET ['timezones']) ? $_GET ['timezones'] : array (
                array (
                        'country' => 'FR'
                ),
                array (
                        'country' => 'BE'
                ),
                false
        ));
        $nbDays = (isset($_GET ['nbDays']) ? $_GET ['nbDays'] : 0);
        $results = ShopymindClient_Callback::getBirthdayClients($shopIdShop, $dateReference, $timezones, $nbDays, $justCount);
    break;
    case 'getBirthdayClientsSignUp' :
        $dateReference = $_GET ['dateReference'];
        $timezones = (isset($_GET ['timezones']) ? $_GET ['timezones'] : array (

                false
        ));
        $results = ShopymindClient_Callback::getBirthdayClientsSignUp($shopIdShop, $dateReference, $timezones, $justCount);
    break;
    case 'getInactiveClients' :
        $dateReference = $_GET ['dateReference'];
        $timezones = (isset($_GET ['timezones']) ? $_GET ['timezones'] : array (
                array (
                        'country' => 'FR'
                ),
                false
        ));
        $nbMonthsLastOrder = (isset($_GET ['nbMonthsLastOrder']) ? $_GET ['nbMonthsLastOrder'] : 0);
        $relaunchOlder = (isset($_GET ['relaunchOlder']) ? $_GET ['relaunchOlder'] : false);
        $results = ShopymindClient_Callback::getInactiveClients($shopIdShop, $dateReference, $timezones, $nbMonthsLastOrder, $relaunchOlder, false);
    break;

    case 'getDroppedOutCart' :
        $nbSeconds = $_GET ['nbSeconds'];
        $customerType = (isset($_GET ['customerType']) ? $_GET ['customerType'] : false);
        $results = ShopymindClient_Callback::getDroppedOutCart($shopIdShop, $nbSeconds);
    break;

    case 'getGoodClientsByAmount' :
        $dateReference = $_GET ['dateReference'];
        $timezones = (isset($_GET ['timezones']) ? $_GET ['timezones'] : array (
                array (
                        'country' => 'FR'
                ),
                false
        ));
        $amount = $_GET ['amount'];
        $amountMax = $_GET ['amountMax'];
        $duration = (isset($_GET ['duration']) ? $_GET ['duration'] : 15);
        $nbDaysLastOrder = (isset($_GET ['nbDaysLastOrder']) ? $_GET ['nbDaysLastOrder'] : 15);
        $relaunchOlder = (isset($_GET ['relaunchOlder']) ? $_GET ['relaunchOlder'] : false);
        $results = ShopymindClient_Callback::getGoodClientsByAmount($shopIdShop, $dateReference, $timezones, $amount, $amountMax, $duration, $nbDaysLastOrder, $relaunchOlder, $justCount);
    break;

    case 'getGoodClientsByNumberOrders' :
        $dateReference = $_GET ['dateReference'];
        $timezones = (isset($_GET ['timezones']) ? $_GET ['timezones'] : array (
                array (
                        'country' => 'FR'
                ),
                false
        ));
        $nbOrder = $_GET ['nbOrder'];
        $nbOrderMax = $_GET ['nbOrderMax'];
        $duration = (isset($_GET ['duration']) ? $_GET ['duration'] : 15);
        $nbDaysLastOrder = (isset($_GET ['nbDaysLastOrder']) ? $_GET ['nbDaysLastOrder'] : 15);
        $relaunchOlder = (isset($_GET ['relaunchOlder']) ? $_GET ['relaunchOlder'] : false);
        $results = ShopymindClient_Callback::getGoodClientsByNumberOrders($shopIdShop, $dateReference, $timezones, $nbOrder, $nbOrderMax, $duration, $nbDaysLastOrder, $relaunchOlder, $justCount);
    break;

    case 'getMissingClients' :
        $dateReference = $_GET['dateReference'];
        $timezones = (isset($_GET ['timezones']) ? $_GET ['timezones'] : array (
                array (
                        'country' => 'FR'
                ),
                false
        ));
        $nbDays = (isset($_GET ['nbDays']) ? $_GET ['nbDays'] : 0);
        $relaunchOlder = (isset($_GET ['relaunchOlder']) ? $_GET ['relaunchOlder'] : false);
        $results = ShopymindClient_Callback::getMissingClients($shopIdShop, $dateReference, $timezones, $nbDays, $relaunchOlder, $justCount);
    break;

    case 'getOrdersByStatus' :
        $dateReference = $_GET['dateReference'];
        $timezones = (isset($_GET ['timezones']) ? $_GET ['timezones'] : array (
                array (
                        'country' => 'FR'
                ),
                false
        ));
        $nbDays = (isset($_GET ['nbDays']) ? $_GET ['nbDays'] : 0);
        $idStatus = $_GET ['idStatus'];
        $results = ShopymindClient_Callback::getOrdersByStatus($shopIdShop, $dateReference, $timezones, $nbDays, $idStatus, $justCount);
    break;

    case 'getVoucherUnused' :
        $dateReference = $_GET ['dateReference'];
        $timezones = (isset($_GET ['timezones']) ? $_GET ['timezones'] : array (
                array (
                        'country' => 'FR'
                ),
                false
        ));
        $nbDayExpiration = (isset($_GET ['nbDayExpiration']) ? $_GET ['nbDayExpiration'] : 0);
        $startBy = (isset($_GET ['startBy']) ? $_GET ['startBy'] : false);
        $results = ShopymindClient_Callback::getVoucherUnused($shopIdShop, $dateReference, $timezones, $nbDayExpiration, $startBy, $justCount = false);
    break;

    case 'getOrderStatus' :
        $results = ShopymindClient_Callback::getOrdersStatus();
    break;

    case 'getTimezones' :
        $results = ShopymindClient_Callback::getTimezones($shopIdShop,false);
    break;

    case 'getUser' :
        $id = $_GET ['userId'];
        $results = ShopymindClient_Callback::getUser($id);
    break;

    case 'generateVoucher' :
        $results = ShopymindClient_Callback::generateVoucher(1, 'amount', '10', 'EUR', 100, 10, '');
    break;

    case 'newOrder' :
        $id_order = $_GET ['id'];
        $order = new Order($id_order);
        $results = ShopymindClient_Callback::checkNewOrder($order, true);
    break;

    case 'getProducts' :
        $results = ShopymindClient_Callback::getProducts($shopIdShop,'fr', false, true);
    break;

    case 'getTestData' :
        $results = ShopymindClient_Callback::getTestData($shopIdShop);
    break;

    case 'findProducts' :
        $lang = $_GET ['lang'];
        $search = $_GET ['s'];
        $results = ShopymindClient_Callback::findProducts($shopIdShop,$lang, $search);
    break;
    case 'findCategories' :
        $lang = $_GET ['lang'];
        $search = $_GET ['s'];
        $results = ShopymindClient_Callback::findCategories($shopIdShop,$lang, $search);
    break;
    case 'findManufacturers' :
        $lang = $_GET ['lang'];
        $search = $_GET ['s'];
        $results = ShopymindClient_Callback::findManufacturers($shopIdShop,$lang, $search);
    break;

    case 'getExistingEmails' :
        $start = $_GET ['start'];
        $limit = $_GET ['limit'];
        $lastUpdate = $_GET ['lastUpdate'];
        $results = ShopymindClient_Callback::getExistingEmails($shopIdShop, $start, $limit, $lastUpdate);
    break;

    case 'getContacts' :
       $start = $_GET ['start'];
        $limit = $_GET ['limit'];
        $lastUpdate = $_GET ['lastUpdate'];
        $results = ShopymindClient_Callback::getContacts($shopIdShop, $start, $limit, $lastUpdate, $justCount);
    break;

    case 'getCarts' :
        $idCart = $_GET ['idCart'];
        $results = ShopymindClient_Callback::getCarts($shopIdShop, $idCart);
    break;

    case 'getCarriers' :
        $results = ShopymindClient_Callback::getCarriers();
    break;

    case 'getCurrencies' :
        $results = ShopymindClient_Callback::getCurrencies();
    break;
    case 'getCountries' :
        $results = ShopymindClient_Callback::getCountries();
    break;
    case 'getLangs' :
        $results = ShopymindClient_Callback::getLangs($shopIdShop);
    break;
    case 'getShops' :
        $results = ShopymindClient_Callback::getShops();
    break;
    case 'getOrder' :
        $idOrder = $_GET ['idOrder'];
        $results = ShopymindClient_Callback::getOrder($idOrder);
    break;
}
echo '<pre>';
print_r($results);
echo '</pre>';
?>