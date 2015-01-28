<?php
/**
 * Call
 *
 * @package     ShopymindClient
 * @copyright   Copyright (c) 2013 - IDVIVE SARL (http://www.idvive.com)
 * @license     New BSD license (http://license.idvive.com)
 * @author      Yannick Dalbin <yannick@idvive.com>
 * @version     $Id call.php 2014-12-17$
 */
if (! isset($SHOPYMIND_CLIENT_CONFIGURATION)) {
    global $SHOPYMIND_CLIENT_CONFIGURATION;
    $SHOPYMIND_CLIENT_CONFIGURATION = array_merge_recursive(require dirname(__FILE__) . '/Src/definitions.php', require dirname(__FILE__) . '/configuration.php');
}

require_once dirname(__FILE__) . '/Src/Server.php';

$server = new ShopymindClient_Src_Server();

if ($server->isValid() === true) {
    if ($server->getTypeRequest() === 'sayHello') {
        $config = $SHOPYMIND_CLIENT_CONFIGURATION;
        $server->sendResponse(array (
                'version' => $config ['version']
        ));
    } elseif ($server->getTypeRequest() === 'orderStatus') {
        require_once dirname(__FILE__) . '/Callback.php';
        if (method_exists('ShopymindClient_Callback', 'getOrdersStatus')) {
            $server->sendResponse(array (
                    'status' => ShopymindClient_Callback::getOrdersStatus()
            ), true);
        }
    } elseif ($server->getTypeRequest() === 'getTimezones') {
        require_once dirname(__FILE__) . '/Callback.php';
        if (method_exists('ShopymindClient_Callback', 'getTimezones')) {
            $params = $server->retrieveParams();
            $server->sendResponse(array (
                    'lastUpdateTimezone' => date('Y-m-d H:i:s'),
                    'timezones' => ShopymindClient_Callback::getTimezones($params ['lastUpdate'])
            ), true);
        }
    } elseif ($server->getTypeRequest() === 'dailyUpdate') {
        require_once dirname(__FILE__) . '/Callback.php';
        $response = array ();
        $params = $server->retrieveParams();
        if (method_exists('ShopymindClient_Callback', 'deleteUnusedVouchers')) {
            $response ['deleteUnusedVouchers'] = ShopymindClient_Callback::deleteUnusedVouchers();
        }
        $server->sendResponse($response, true);
    } elseif ($server->getTypeRequest() === 'getTestData') {
        require_once dirname(__FILE__) . '/Callback.php';
        if (method_exists('ShopymindClient_Callback', 'getTestData')) {
            $params = $server->retrieveParams();
            $server->sendResponse(array (
                    'testData' => ShopymindClient_Callback::getTestData((isset($params ['lang']) ? $params ['lang'] : false))
            ), true);
        }
        $server->sendResponse($response, true);
    } elseif ($server->getTypeRequest() === 'generateVouchers') {
        require_once dirname(__FILE__) . '/Callback.php';
        if (method_exists('ShopymindClient_Callback', 'generateVouchers')) {
            $params = $server->retrieveParams();
            $server->sendResponse(array (
                    'vouchers' => ShopymindClient_Callback::generateVouchers($params ['voucherInfos'], $params ['voucherEmails'])
            ), true);
        }
    } elseif ($server->getTypeRequest() === 'generateKeysAccess') {
        require_once dirname(__FILE__) . '/Callback.php';
        if (method_exists('ShopymindClient_Callback', 'generateKeysAccess')) {
            $params = $server->retrieveParams();
            ShopymindClient_Callback::generateKeysAccess($params ['keysAccess']);
            $server->sendResponse(array (), true);
        }
    } elseif ($server->getTypeRequest() === 'getCountries') {
        require_once dirname(__FILE__) . '/Callback.php';
        if (method_exists('ShopymindClient_Callback', 'getCountries')) {
            $params = $server->retrieveParams();
            $server->sendResponse(array (
                    'countries' => ShopymindClient_Callback::getCountries()
            ), true);
        }
    } elseif ($server->getTypeRequest() === 'getCurrencies') {
        require_once dirname(__FILE__) . '/Callback.php';
        if (method_exists('ShopymindClient_Callback', 'getCurrencies')) {
            $params = $server->retrieveParams();
            $server->sendResponse(array (
                    'currencies' => ShopymindClient_Callback::getCurrencies()
            ), true);
        }
    } elseif ($server->getTypeRequest() === 'getCustomerGroups') {
        require_once dirname(__FILE__) . '/Callback.php';
        if (method_exists('ShopymindClient_Callback', 'getCustomerGroups')) {
            $params = $server->retrieveParams();
            $server->sendResponse(array (
                    'customerGroups' => ShopymindClient_Callback::getCustomerGroups()
            ), true);
        }
    } elseif ($server->getTypeRequest() === 'getShopLangs') {
        require_once dirname(__FILE__) . '/Callback.php';
        if (method_exists('ShopymindClient_Callback', 'getShopLangs')) {
            $params = $server->retrieveParams();
            $server->sendResponse(array (
                    'lastUpdateTimezone' => date('Y-m-d H:i:s'),
                    'timezones' => ShopymindClient_Callback::getTimezones($params ['lastUpdate'])
            ), true);
        }
    } elseif ($server->getTypeRequest() === 'getProducts') {
        require_once dirname(__FILE__) . '/Callback.php';
        if (method_exists('ShopymindClient_Callback', 'getProducts')) {
            $params = $server->retrieveParams();
            $server->sendResponse(array (
                    'articles' => ShopymindClient_Callback::getProducts($params ['lang'], (isset($params ['products']) ? $params ['products'] : false), (isset($params ['random']) ? $params ['random'] : false), (isset($params ['maxProducts']) ? $params ['maxProducts'] : null))
            ), true);
        }
    } elseif ($server->getTypeRequest() === 'relaunch') {
        $relaunch = $server->retrieveRelaunch();
        $params = $server->retrieveParams();

        if ($relaunch !== null) {
            if (file_exists('Src/Reminders/' . ucfirst($relaunch) . '.php')) {
                require_once dirname(__FILE__) . '/Src/Reminders/' . ucfirst($relaunch) . '.php';
                $classRelaunch = 'ShopymindClient_Src_Reminders_' . ucfirst($relaunch);
                $relaunch = call_user_func(array (
                        $classRelaunch,
                        'factory'
                ), $params);
                if ($relaunch !== null && ! is_string($relaunch)) {
                    $response = $relaunch->get();

                    if ($response !== null && is_array($response)) {
                        $server->sendResponse(array (
                                'clients' => $response
                        ));
                    } else {
                        $server->sendResponse(array (
                                'error' => $response
                        ), false);
                    }
                } elseif (is_string($relaunch)) {
                    $server->sendResponse(array (
                            'error' => $relaunch
                    ), false);
                }
            }
        }
    }
}

$server->sendResponse(array (), false);
