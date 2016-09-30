<?php

if (! isset($SHOPYMIND_CLIENT_CONFIGURATION)) {
    global $SHOPYMIND_CLIENT_CONFIGURATION;
    $SHOPYMIND_CLIENT_CONFIGURATION = array_merge_recursive(require dirname(__FILE__) . '/Src/definitions.php', require dirname(__FILE__) . '/configuration.php');
}

require_once dirname(__FILE__) . '/Src/Server.php';

$server = new ShopymindClient_Src_Server();

try {
    if ($server->isValid() === true) {
        if ($server->getTypeRequest() === 'sayHello') {
            $config = $SHOPYMIND_CLIENT_CONFIGURATION;
            $server->sendResponse(array(
                'version' => $config['version']
            ));
        } elseif ($server->getTypeRequest() === 'orderStatus') {
            require_once dirname(__FILE__) . '/Callback.php';
            if (method_exists('ShopymindClient_Callback', 'getOrdersStatus')) {
                $server->sendResponse(array(
                    'status' => ShopymindClient_Callback::getOrdersStatus()
                ), true);
            }
        } elseif ($server->getTypeRequest() === 'getTimezones') {
            require_once dirname(__FILE__) . '/Callback.php';
            if (method_exists('ShopymindClient_Callback', 'getTimezones')) {
                $params = $server->retrieveParams();
                $server->sendResponse(array(
                    'lastUpdateTimezone' => date('Y-m-d H:i:s'),
                    'timezones' => ShopymindClient_Callback::getTimezones((isset($params['shopIdShop']) ? $params['shopIdShop'] : false), $params['lastUpdate'])
                ), true);
            }
        } elseif ($server->getTypeRequest() === 'dailyUpdate') {
            require_once dirname(__FILE__) . '/Callback.php';
            $response = array();
            $params = $server->retrieveParams();
            if (method_exists('ShopymindClient_Callback', 'deleteUnusedVouchers')) {
                $response['deleteUnusedVouchers'] = ShopymindClient_Callback::deleteUnusedVouchers();
            }
            $server->sendResponse($response, true);
        } elseif ($server->getTypeRequest() === 'getTestData') {
            require_once dirname(__FILE__) . '/Callback.php';
            if (method_exists('ShopymindClient_Callback', 'getTestData')) {
                $params = $server->retrieveParams();
                $server->sendResponse(array(
                    'testData' => ShopymindClient_Callback::getTestData((isset($params['shopIdShop']) ? $params['shopIdShop'] : false), (isset($params['lang']) ? $params['lang'] : false))
                ), true);
            }
            $server->sendResponse($response, true);
        } elseif ($server->getTypeRequest() === 'generateVouchers') {
            require_once dirname(__FILE__) . '/Callback.php';
            if (method_exists('ShopymindClient_Callback', 'generateVouchers')) {
                $params = $server->retrieveParams();				
                $server->sendResponse(array(
                    'vouchers' => ShopymindClient_Callback::generateVouchers(
                        $params['voucherInfos'],
                        $params['voucherEmails'],
                        (isset($params['shopIdShop']) ? $params['shopIdShop'] : false),
                        (isset($params['voucherInfos']['dynamicPrefix']) ? $params['voucherInfos']['dynamicPrefix'] : false),
                        (isset($params['voucherInfos']['duplicateCode']) ? $params['voucherInfos']['duplicateCode'] : false)
                )
                ), true);
            }
        } elseif ($server->getTypeRequest() === 'getCountries') {
            require_once dirname(__FILE__) . '/Callback.php';
            if (method_exists('ShopymindClient_Callback', 'getCountries')) {
                $params = $server->retrieveParams();
                $server->sendResponse(array(
                    'countries' => ShopymindClient_Callback::getCountries()
                ), true);
            }
        } elseif ($server->getTypeRequest() === 'getCurrencies') {
            require_once dirname(__FILE__) . '/Callback.php';
            if (method_exists('ShopymindClient_Callback', 'getCurrencies')) {
                $params = $server->retrieveParams();
                $server->sendResponse(array(
                    'currencies' => ShopymindClient_Callback::getCurrencies()
                ), true);
            }
        } elseif ($server->getTypeRequest() === 'getCustomerGroups') {
            require_once dirname(__FILE__) . '/Callback.php';
            if (method_exists('ShopymindClient_Callback', 'getCustomerGroups')) {
                $params = $server->retrieveParams();
                $server->sendResponse(array(
                    'customerGroups' => ShopymindClient_Callback::getCustomerGroups((isset($params['shopIdShop']) ? $params['shopIdShop'] : false))
                ), true);
            }
        } elseif ($server->getTypeRequest() === 'getCarriers') {
            require_once dirname(__FILE__) . '/Callback.php';
            if (method_exists('ShopymindClient_Callback', 'getCarriers')) {
                $params = $server->retrieveParams();
                $server->sendResponse(array(
                    'carriers' => ShopymindClient_Callback::getCarriers()
                ), true);
            }
        } elseif ($server->getTypeRequest() === 'getLangs') {
            require_once dirname(__FILE__) . '/Callback.php';
            if (method_exists('ShopymindClient_Callback', 'getLangs')) {
                $params = $server->retrieveParams();
                $server->sendResponse(array(
                    'langs' => ShopymindClient_Callback::getLangs((isset($params['shopIdShop']) ? $params['shopIdShop'] : false))
                ), true);
            }
        } elseif ($server->getTypeRequest() === 'getProducts') {
            require_once dirname(__FILE__) . '/Callback.php';
            if (method_exists('ShopymindClient_Callback', 'getProducts')) {
                $params = $server->retrieveParams();
                $server->sendResponse(array(
                    'products' => ShopymindClient_Callback::getProducts((isset($params['shopIdShop']) ? $params['shopIdShop'] : false), $params['lang'], (isset($params['products']) ? $params['products'] : false), (isset($params['random']) ? $params['random'] : false), (isset($params['maxProducts']) ? $params['maxProducts'] : null))
                ), true);
            }
        } elseif ($server->getTypeRequest() === 'findProducts') {
            require_once dirname(__FILE__) . '/Callback.php';
            if (method_exists('ShopymindClient_Callback', 'findProducts')) {
                $params = $server->retrieveParams();
                $server->sendResponse(array(
                    'products' => ShopymindClient_Callback::findProducts((isset($params['shopIdShop']) ? $params['shopIdShop'] : false), $params['lang'], $params['search'])
                ), true);
            }
        } elseif ($server->getTypeRequest() === 'findCategories') {
            require_once dirname(__FILE__) . '/Callback.php';
            if (method_exists('ShopymindClient_Callback', 'findCategories')) {
                $params = $server->retrieveParams();
                $server->sendResponse(array(
                    'categories' => ShopymindClient_Callback::findCategories((isset($params['shopIdShop']) ? $params['shopIdShop'] : false), $params['lang'], $params['search'])
                ), true);
            }
        } elseif ($server->getTypeRequest() === 'findManufacturers') {
            require_once dirname(__FILE__) . '/Callback.php';
            if (method_exists('ShopymindClient_Callback', 'findManufacturers')) {
                $params = $server->retrieveParams();
                $server->sendResponse(array(
                    'manufacturers' => ShopymindClient_Callback::findManufacturers((isset($params['shopIdShop']) ? $params['shopIdShop'] : false), $params['lang'], $params['search'])
                ), true);
            }
        } elseif ($server->getTypeRequest() === 'getExistingEmails') {
            require_once dirname(__FILE__) . '/Callback.php';
            if (method_exists('ShopymindClient_Callback', 'getExistingEmails')) {
                $params = $server->retrieveParams();
                $server->sendResponse(array(
                    'lastUpdate' => date('Y-m-d H:i:s'),
                    'emails' => ShopymindClient_Callback::getExistingEmails((isset($params['shopIdShop']) ? $params['shopIdShop'] : false),(isset($params['start']) ? $params['start'] : false),(isset($params['limit']) ? $params['limit'] : false),(isset($params['lastUpdate']) ? $params['lastUpdate'] : false))
                ), true);
            }
        }
        /**
         *  SYNC REQUESTS   ------------
         */
        elseif ($server->getTypeRequest() === 'syncCustomers') {
            require_once dirname(__FILE__) . '/Callback.php';
            if (method_exists('ShopymindClient_Callback', 'syncCustomers')) {
                $params = $server->retrieveParams();
                $server->sendResponse(array(
                    'lastUpdate' => date('Y-m-d H:i:s'),
                    'customers' => ShopymindClient_Callback::syncCustomers((isset($params['shopIdShop']) ? $params['shopIdShop'] : false),(isset($params['start']) ? $params['start'] : false),(isset($params['limit']) ? $params['limit'] : false),(isset($params['lastUpdate']) ? $params['lastUpdate'] : false),(isset($params['idCustomer']) ? $params['idCustomer'] : false),(isset($params['justCount']) ? $params['justCount'] : false))
                ), true);
            }
        } elseif ($server->getTypeRequest() === 'syncProducts') {
            require_once dirname(__FILE__) . '/Callback.php';
            if (method_exists('ShopymindClient_Callback', 'syncProducts')) {
                $params = $server->retrieveParams();
                $server->sendResponse(array(
                    'lastUpdate' => date('Y-m-d H:i:s'),
                    'products' => ShopymindClient_Callback::syncProducts((isset($params['shopIdShop']) ? $params['shopIdShop'] : false),(isset($params['start']) ? $params['start'] : false),(isset($params['limit']) ? $params['limit'] : false),(isset($params['lastUpdate']) ? $params['lastUpdate'] : false),(isset($params['justCount']) ? $params['justCount'] : false))
                ), true);
            }
        } elseif ($server->getTypeRequest() === 'syncProductsCategories') {
            require_once dirname(__FILE__) . '/Callback.php';
            if (method_exists('ShopymindClient_Callback', 'syncProductsCategories')) {
                $params = $server->retrieveParams();
                $server->sendResponse(array(
                    'lastUpdate' => date('Y-m-d H:i:s'),
                    'productsCategories' => ShopymindClient_Callback::syncProductsCategories((isset($params['shopIdShop']) ? $params['shopIdShop'] : false),(isset($params['start']) ? $params['start'] : false),(isset($params['limit']) ? $params['limit'] : false),(isset($params['lastUpdate']) ? $params['lastUpdate'] : false),(isset($params['justCount']) ? $params['justCount'] : false))
                ), true);
            }
        } elseif ($server->getTypeRequest() === 'syncOrders') {
            require_once dirname(__FILE__) . '/Callback.php';
            if (method_exists('ShopymindClient_Callback', 'syncOrders')) {
                $params = $server->retrieveParams();
                $server->sendResponse(array(
                    'lastUpdate' => date('Y-m-d H:i:s'),
                    'orders' => ShopymindClient_Callback::syncOrders((isset($params['shopIdShop']) ? $params['shopIdShop'] : false),(isset($params['start']) ? $params['start'] : false),(isset($params['limit']) ? $params['limit'] : false),(isset($params['lastUpdate']) ? $params['lastUpdate'] : false),(isset($params['idOrder']) ? $params['idOrder'] : false),(isset($params['justCount']) ? $params['justCount'] : false))
                ), true);
            }
        }
        /**
         *  END SYNC REQUESTS   ----------
         */

        elseif ($server->getTypeRequest() === 'getUser') {
            require_once dirname(__FILE__) . '/Callback.php';
            if (method_exists('ShopymindClient_Callback', 'getUser')) {
                $params = $server->retrieveParams();
                if(isset($params['idUser']) && $params['idUser'])
                    $server->sendResponse(array(
                        'user' => ShopymindClient_Callback::getUser($params['idUser'])
                    ), true);
            }
        } elseif ($server->getTypeRequest() === 'getCarts') {
            require_once dirname(__FILE__) . '/Callback.php';
            if (method_exists('ShopymindClient_Callback', 'getCarts')) {
                $params = $server->retrieveParams();
                if(isset($params['idCart']) && $params['idCart'])
                    $server->sendResponse(array(
                        'carts' => ShopymindClient_Callback::getCarts((isset($params['shopIdShop']) ? $params['shopIdShop'] : false),$params['idCart'])
                    ), true);
            }
        } elseif ($server->getTypeRequest() === 'getOrder') {
            require_once dirname(__FILE__) . '/Callback.php';
            if (method_exists('ShopymindClient_Callback', 'getOrder')) {
                $params = $server->retrieveParams();
                if(isset($params['idOrder']) && $params['idOrder'])
                    $server->sendResponse(array(
                        'order' => ShopymindClient_Callback::getOrder($params['idOrder'])
                    ), true);
            }
        } elseif ($server->getTypeRequest() === 'getShops') {
            require_once dirname(__FILE__) . '/Callback.php';
            if (method_exists('ShopymindClient_Callback', 'getShops')) {
                $params = $server->retrieveParams();
                $server->sendResponse(array(
                    'shops' => ShopymindClient_Callback::getShops()
                ), true);
            }
        } elseif ($server->getTypeRequest() === 'relaunch') {
            $relaunch = $server->retrieveRelaunch();
            $params = $server->retrieveParams();

            if ($relaunch !== null) {				
                if (file_exists(dirname(__FILE__) . '/Src/Reminders/' . ucfirst($relaunch) . '.php')) {
                    require_once dirname(__FILE__) . '/Src/Reminders/' . ucfirst($relaunch) . '.php';
                    $classRelaunch = 'ShopymindClient_Src_Reminders_' . ucfirst($relaunch);
                    $relaunch = call_user_func(array(
                        $classRelaunch,
                        'factory'
                    ), $params);
                    if ($relaunch !== null && ! is_string($relaunch)) {
                        $response = $relaunch->get();
						
                        if ($response !== null && is_array($response)) {
                            $server->sendResponse(array(
                                'clients' => $response
                            ));
                        } else {
                            $server->sendResponse(array(
                                'error' => $response
                            ), false);
                        }
                    } elseif (is_string($relaunch)) {
                        $server->sendResponse(array(
                            'error' => $relaunch
                        ), false);
                    }
                }
            }
        }
    }
} catch (Exception $e) {
    $server->sendResponse(array(
        'success' => false,
        'message' => $e->getMessage(),
        'stacktrace' => $e->getTrace(),
    ));
}

$server->sendResponse(array(), false);
