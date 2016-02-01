<?php
/**
 * SPM_ShopyMind_Helper_Data
 *
 * @package     SPM_ShopyMind
 * @copyright   Copyright (c) 2014 - ShopyMind SARL (http://www.shopymind.com)
 * @license     New BSD license (http://license.shopymind.com)
 * @author      Couvert Jean-Sébastien <js.couvert@shopymind.com>
 * @version     $Id Data.php 2014-12-17$
 */
class SPM_ShopyMind_Helper_Data extends Mage_Core_Helper_Abstract
{
    const MANUFACTURER_ATTRIBUTE_CODE = 'manufacturer';

    public function formatCustomerQuote(Mage_Sales_Model_Quote $quote)
    {
        $QuoteFormatter = new SPM_ShopyMind_DataMapper_Quote();

        return $QuoteFormatter->format($quote);
    }

    public function getMagentoAttributeCode($model, $attribute_code) {
        if (class_exists('ShopymindClient_CallbackOverride', false) && method_exists('ShopymindClient_CallbackOverride', __FUNCTION__)) {
            return call_user_func_array(array ('ShopymindClient_CallbackOverride', __FUNCTION__), func_get_args());
        }

        $eavAttribute = new Mage_Eav_Model_Mysql4_Entity_Attribute();
        return $eavAttribute->getIdByCode($model, $attribute_code);
    }

    public function getUrl($route)
    {
        return Mage::getUrl($route, array('_nosid' => true));
    }

    public function productUrlOf(Mage_Catalog_Model_Product $product)
    {
        return $product->getProductUrl(false);
    }

    public function productImageUrlOf(Mage_Catalog_Model_Product $product)
    {
        return $product->getSmallImageUrl(200, 200);
    }

    public function manufacturerIdOf(Mage_Catalog_Model_Product $product)
    {
        return $product->getData(self::MANUFACTURER_ATTRIBUTE_CODE);
    }

    public function formatDateTime($date)
    {
        return date('Y-m-d H:i:s', strtotime($date));
    }

    public function formatCombinationsOfProduct(Mage_Catalog_Model_Product $product, $formatter, $attributeNames = null, $parentSuperAttributes = null)
    {
        $combinations = array();
        if ($product->getTypeId() === Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {

            $ids = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($product->getId());
            $childProducts = Mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToFilter('entity_id', $ids);

            $attributesToJoin = array();
            if (!empty($parentSuperAttributes)) {
                $attributesToJoin = array_map(function($attribute) { return $attribute['attribute_code']; }, $parentSuperAttributes);

                foreach($attributesToJoin as $attributeToJoin) {
                    $childProducts->joinAttribute($attributeToJoin, 'catalog_product/' . $attributeToJoin, 'entity_id', null, 'left');
                }
            }


            $attributeNames = array_merge(
                $attributesToJoin,
                $attributeNames
            );

            if (!empty($attributeNames)) {
                $childProducts->addAttributeToSelect($attributeNames);
            }

            $combinations = array_values(array_map(
                $formatter,
                iterator_to_array($childProducts->getIterator())
            ));
        }
        return $combinations;
    }

    public function shortId($length = 6)
    {
        $az = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $azr = rand(0, 51);
        $azs = substr($az, $azr, 10);
        $stamp = hash('sha256', time());
        $mt = hash('sha256', mt_rand(5, 20));
        $alpha = hash('sha256', $azs);
        $hash = str_shuffle($stamp . $mt . $alpha);
        $code = ucfirst(substr($hash, $azr, $length));

        return $code;
    }

    public function startEmulatingScope(SPM_ShopyMind_Model_Scope $scope)
    {
        $storeIds = $scope->storeIds();
        $appEmulation = Mage::getSingleton('core/app_emulation');
        return $appEmulation->startEnvironmentEmulation($storeIds[0]);
    }

    public function stopEmulation($emulatedEnvironment)
    {
        Mage::getSingleton('core/app_emulation')->stopEnvironmentEmulation($emulatedEnvironment);
    }

}
