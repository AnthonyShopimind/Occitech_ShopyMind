<?php

class SPM_ShopyMind_DataMapper_Product
{
    /**
     * @var $helper SPM_ShopyMind_Helper_Data
     */
    private $helper;

    public function __construct()
    {
        $this->helper = Mage::helper('shopymind');
    }

    public function format(Mage_Catalog_Model_Product $product, SPM_ShopyMind_Model_Scope $scope = null)
    {
        if (!$product->getId()) {
            return array();
        }

        if (is_null($scope)) {
            $scope = SPM_ShopyMind_Model_Scope::buildUnrestricted();
        }

        $shopymindData = array(
            'shop_id_shop' => $scope->getId(),
            'id_product' => $product->getId(),
            'reference' => $product->getSku(),
            'lang' => $scope->getLang(),
            'name' => $product->getName(),
            'description_short' => $product->getShortDescription(),
            'description' => $product->getDescription(),
            'product_link' => $this->helper->productUrlOf($product),
            'image_link' => $this->helper->productImageUrlOf($product),
            'combinations' => $this->formattedCombinationsOf($product),
            'id_categories' => $product->getCategoryIds(),
            'id_manufacturer' => $this->helper->manufacturerIdOf($product),
            'currency' => $scope->currencyCode(),
            'price' => $product->getPrice(),
            'price_discount' => $product->getFinalPrice(),
            'quantity_remaining' => $product->getStockItem()->getQty(),
            'date_creation' => $this->helper->formatDateTime($product->getCreatedAt()),
            'active' => $product->getIsSalable(),
        );

        return $shopymindData;
    }

    private function formattedCombinationsOf(Mage_Catalog_Model_Product $product)
    {
        $formatter = function ($childProduct) {
            return array(
                'id' => $childProduct->getId()
            );
        };
        return $this->helper->formatCombinationsOfProduct($product, $formatter);
    }
}
