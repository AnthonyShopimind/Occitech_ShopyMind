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

    public function format(Mage_Catalog_Model_Product $product)
    {
        if (!$product->getId()) {
            return array();
        }

        $shopymindData = array_merge(
            $this->formatProductCommonData($product),
            array(
                'id_product' => $product->getId(),
                'name' => $product->getName(),
                'description_short' => $product->getShortDescription(),
                'description' => $product->getDescription(),
                'combinations' => $this->formattedCombinationsOf($product),
                'id_categories' => $product->getCategoryIds(),
                'id_manufacturer' => $this->helper->manufacturerIdOf($product),
                'quantity_remaining' => $product->getStockItem()->getQty(),
                'date_creation' => $this->helper->formatDateTime($product->getCreatedAt()),
                'active' => (bool) $product->getIsSalable(),
            )
        );

        return $shopymindData;
    }

    private function formattedCombinationsOf(Mage_Catalog_Model_Product $product)
    {
        $attributeNames = array('name', 'stock_item', 'price', 'final_price');

        $commonDataFormatter = array($this, 'formatProductCommonData');
        $formatter = function (Mage_Catalog_Model_Product $childProduct) use ($commonDataFormatter) {
            $stockItemModel = Mage::getModel('cataloginventory/stock_item');
            return array_merge(
                call_user_func($commonDataFormatter, $childProduct),
                array(
                    'combination_name' => $childProduct->getName(),
                    'id_combination' => $childProduct->getId(),
                    'quantity_remaining' => $stockItemModel->loadByProduct($childProduct)->getQty(),
                )
            );
        };
        return $this->helper->formatCombinationsOfProduct($product, $formatter, $attributeNames);
    }

    public function formatProductCommonData(Mage_Catalog_Model_Product $product)
    {
        return array(
            'reference' => $product->getSku(),
            'product_link' => $this->helper->productUrlOf($product),
            'image_link' => $this->helper->productImageUrlOf($product),
            'price' => $product->getPrice(),
            'price_discount' => $product->getFinalPrice(),
        );
    }
}
