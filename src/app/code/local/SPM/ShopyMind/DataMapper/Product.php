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

    public function formatProductWithCombination(Mage_Catalog_Model_Product $product)
    {
        if (!$product->getId()) {
            return array();
        }

        $combinationData = array();
        $CombinationFormatter = new SPM_ShopyMind_DataMapper_Combination();
        if ($CombinationFormatter->isApplicable($product)) {
            $combinationData = $CombinationFormatter->formatSelectedCombinationData($product);
        } else {
            $combinationData['combinations'] = $this->formattedCombinationsOf($product);
        }

        $product->unsetData('is_salable');
        $shopymindData = array_merge(
            $this->formatProductCommonData($product),
            array(
                'id_product' => $product->getId(),
                'name' => $product->getName(),
                'description_short' => $product->getShortDescription(),
                'description' => $product->getDescription(),
                'id_categories' => $product->getCategoryIds(),
                'id_manufacturer' => $this->helper->manufacturerIdOf($product),
                'date_creation' => $this->helper->formatDateTime($product->getCreatedAt()),
                'active' => (bool) $product->getIsSalable(),
                'quantity_remaining' => $product->getStockItem()->getQty(),
            ),
            $combinationData
        );

        return $shopymindData;
    }

    private function formattedCombinationsOf(Mage_Catalog_Model_Product $product)
    {
        $parentSuperAttributes = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
        $attributeNames = array('name', 'stock_item', 'price', 'final_price');

        $commonDataFormatter = array($this, 'formatProductCommonData');
        $childAttributesFormatter = array($this, 'childAttributesFormatter');
        $formatter = function (Mage_Catalog_Model_Product $childProduct) use ($commonDataFormatter, $childAttributesFormatter, $parentSuperAttributes) {
            $stockItemModel = Mage::getModel('cataloginventory/stock_item');
            return array_merge(
                call_user_func($commonDataFormatter, $childProduct),
                array(
                    'combination_name' => $childProduct->getName(),
                    'id_combination' => $childProduct->getId(),
                    'quantity_remaining' => $stockItemModel->loadByProduct($childProduct)->getQty(),
                    'values' => call_user_func($childAttributesFormatter, $childProduct, $parentSuperAttributes),
                )
            );
        };
        return $this->helper->formatCombinationsOfProduct($product, $formatter, $attributeNames, $parentSuperAttributes);
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

    public function childAttributesFormatter(Mage_Catalog_Model_Product $product, $parentSuperAttributes)
    {
        return array_map(function($attribute) use ($product) {
            $label = '';
            foreach($attribute['values'] as $value) {
                if ($product->getData($attribute['attribute_code']) == $value['value_index']) {
                    $label = $value['store_label'];
                    continue;
                }
            }

            return array(
                'name' => $attribute['store_label'],
                'value' => $label
            );
        }, $parentSuperAttributes);
    }
}
