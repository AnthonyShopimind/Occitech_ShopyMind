<?php

/**
 * SPM_ShopyMind_Block_Footer
 *
 * @package     SPM_ShopyMind
 * @copyright   Copyright (c) 2014 - ShopyMind SARL (http://www.shopymind.com)
 * @license     New BSD license (http://license.shopymind.com)
 * @author      Couvert Jean-Sébastien <js.couvert@shopymind.com>
 * @version     $Id Footer.php 2014-12-17$
 */
class SPM_ShopyMind_Block_Footer extends Mage_Core_Block_Template
{
    public static $userInfosCache = null;

    public function __httpBuildQuery($data, $prefix = null, $sep = null, $key = '', $urlencode = true)
    {
        if (!function_exists('http_build_query')) {
            $ret = array();

            foreach ((array)$data as $k => $v) {
                if ($urlencode)
                    $k = urlencode($k);
                if (is_int($k) && $prefix != null)
                    $k = $prefix . $k;
                if (!empty($key))
                    $k = $key . '%5B' . $k . '%5D';
                if ($v === NULL)
                    continue;
                elseif ($v === FALSE)
                    $v = '0';

                if (is_array($v) || is_object($v))
                    array_push($ret, yourls_http_build_query($v, '', $sep, $k, $urlencode));
                elseif ($urlencode)
                    array_push($ret, $k . '=' . urlencode($v));
                else
                    array_push($ret, $k . '=' . $v);
            }

            if (NULL === $sep)
                $sep = ini_get('arg_separator.output');

            return implode($sep, $ret);
        } else
            return http_build_query($data);
    }

    private function _jsonEncode($a)
    {
        if (!function_exists('json_encode')) {
            if (is_null($a))
                return 'null';
            if ($a === false)
                return 'false';
            if ($a === true)
                return 'true';
            if (is_scalar($a)) {
                if (is_float($a)) {
                    // Always use "." for floats.
                    return floatval(str_replace(",", ".", strval($a)));
                }

                if (is_string($a)) {
                    static $jsonReplaces = array(
                        array(
                            "\\",
                            "/",
                            "\n",
                            "\t",
                            "\r",
                            "\b",
                            "\f",
                            '"'
                        ),
                        array(
                            '\\\\',
                            '\\/',
                            '\\n',
                            '\\t',
                            '\\r',
                            '\\b',
                            '\\f',
                            '\"'
                        )
                    );
                    return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
                } else
                    return $a;
            }
            $isList = true;
            for ($i = 0, reset($a); $i < count($a); $i++, next($a)) {
                if (key($a) !== $i) {
                    $isList = false;
                    break;
                }
            }
            $result = array();
            if ($isList) {
                foreach ($a as $v)
                    $result[] = json_encode($v);
                return '[' . join(',', $result) . ']';
            } else {
                foreach ($a as $k => $v)
                    $result[] = json_encode($k) . ':' . json_encode($v);
                return '{' . join(',', $result) . '}';
            }
        } else
            return json_encode($a);
    }

    public function _getSessionCartId()
    {
        $session = Mage::getSingleton('checkout/session');
        $cart_id = $session->getQuote()->getId();
        return $cart_id;
    }

    public function _getCurrentUserInfos()
    {
        if (self::$userInfosCache !== null) return self::$userInfosCache;
        $currentUserInfos = array();
        $cookieVisitorId = Mage::app()->getCookie();
        if (!$cookieVisitorId->get('spm_visitor_id'))
            $cookieVisitorId->set('spm_visitor_id', md5(uniqid()), 657000);
        $url = '//' . $_SERVER ['HTTP_HOST'] . $_SERVER ['REQUEST_URI'];
        $currentUserInfos ['url'] = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerSession = Mage::getSingleton('customer/session')->getCustomer();
            $primaryAddress = $customerSession->getPrimaryShippingAddress();
            $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($customerSession->getEmail());
            $currentUserInfos ['user'] = array(
                'id_customer' => $customerSession->getId(),
                'optin' => '',
                'newsletter' => (int)$subscriber->isSubscribed(),
                'customer_since' => $customerSession->getCreatedAt(),
                'email_address' => md5($customerSession->getEmail()),
                'birthday' => $customerSession->getDob(),
                'gender' => $customerSession->getGender(),
                'postcode' => (is_object($primaryAddress) ? $primaryAddress->getPostcode() : ''),
                'region' => (is_object($primaryAddress) ? $primaryAddress->getRegionCode() : ''),
                'country' => (is_object($primaryAddress) ? $primaryAddress->getCountryId() : ''),
            );
        } else
            $currentUserInfos ['user'] = null;

        // Id du panier

        $currentUserInfos ['id_cart'] = $this->_getSessionCartId();
        $currentUserInfos ['id_product'] = (is_object(Mage::registry('current_product')) ? Mage::registry('current_product')->getId() : '');
        $currentUserInfos ['id_category'] = (is_object(Mage::registry('current_category')) ? Mage::registry('current_category')->getId() : '');
        $currentUserInfos ['id_manufacturer'] = (is_object(Mage::registry('current_product')) ? Mage::registry('current_product')->getManufacturer() : '');
        $currentUserInfos ['spm_ident'] = $this->_getSpmIdent();
        $currentUserInfos ['visitor_id'] = $cookieVisitorId->get('spm_visitor_id');
        self::$userInfosCache = $currentUserInfos;
        return $currentUserInfos;
    }

    public function _getSpmIdent()
    {
        $SHOPYMIND_CLIENT_CONFIGURATION = array();
        try {
            $SHOPYMIND_CLIENT_CONFIGURATION = require Mage::getBaseDir('base') . '/lib/ShopymindClient/configuration.php';
        } catch (Exception $e) {
            Mage::log($e->getMessage(), Zend_Log::ERR);
        }
        return @$SHOPYMIND_CLIENT_CONFIGURATION['api']['identifiant'];
    }

    public function getClientUrl()
    {
        $SHOPYMIND_CLIENT_CONFIGURATION = array();
        try {
            $SHOPYMIND_CLIENT_CONFIGURATION = require Mage::getBaseDir('base') . '/lib/ShopymindClient/Src/definitions.php';
        } catch (Exception $e) {
            Mage::log($e->getMessage(), Zend_Log::ERR);
        }
        return @str_replace('/rest', '', @$SHOPYMIND_CLIENT_CONFIGURATION['api']['url']);
    }

    public function getUserInfosJson()
    {
        return $this->_jsonEncode($this->_getCurrentUserInfos());
    }

    public function getUserInfosEncode()
    {
        return $this->__httpBuildQuery($this->_getCurrentUserInfos());
    }
}
