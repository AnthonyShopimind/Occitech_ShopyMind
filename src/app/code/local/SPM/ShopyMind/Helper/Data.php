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

    public function productUrlOf(Mage_Catalog_Model_Product $product)
    {
        return str_replace(basename($_SERVER ['SCRIPT_NAME']) . '/', '', $product->getProductUrl(false));
    }

    public function productImageUrlOf(Mage_Catalog_Model_Product $product)
    {
        return str_replace(basename($_SERVER ['SCRIPT_NAME']) . '/', '', $product->getSmallImageUrl(200, 200));
    }

    public function manufacturerIdOf(Mage_Catalog_Model_Product $product)
    {
        return $product->getData(self::MANUFACTURER_ATTRIBUTE_CODE);
    }

    public function formatDateTime($date)
    {
        return date('Y-m-d H:i:s', strtotime($date));
    }

    public function formatCombinationsOfProduct(Mage_Catalog_Model_Product $product, $formatter, $attributeIds = null)
    {
        $combinations = array();
        if ($product->getTypeId() === Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
            $combinations = array_map(
                $formatter,
                Mage::getModel('catalog/product_type_configurable')->getUsedProducts($attributeIds, $product)
            );
        }
        return $combinations;
    }

}
