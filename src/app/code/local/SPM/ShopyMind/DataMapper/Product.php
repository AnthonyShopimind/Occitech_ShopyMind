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
        $parentSuperAttributes = null;
        if ($product->getTypeId() === Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
            $parentSuperAttributes = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
        }
        $attributeNames = array('name', 'stock_item', 'price', 'final_price');

        $commonDataFormatter = array($this, 'formatProductCommonData');
        $childAttributesFormatter = array($this, 'childAttributesFormatter');
        $formatter = function (Mage_Catalog_Model_Product $childProduct) use ($commonDataFormatter, $childAttributesFormatter, $parentSuperAttributes) {
            $stockItemModel = Mage::getModel('cataloginventory/stock_item');

            $combinationData = array(
                'combination_name' => $childProduct->getName(),
                'id_combination' => $childProduct->getId(),
                'quantity_remaining' => $stockItemModel->loadByProduct($childProduct)->getQty(),
                'values' => call_user_func($childAttributesFormatter, $childProduct, $parentSuperAttributes),
                'default' => 0,
            );

            if (!$childProduct->isVisibleInSiteVisibility()) {
                $combinationData['product_link'] = str_replace('.html/', '.html', $this->getParentProductUrl($childProduct));
            }

            return array_merge(
                call_user_func($commonDataFormatter, $childProduct),
                $combinationData
            );
        };
        return $this->helper->formatCombinationsOfProduct($product, $formatter, $attributeNames, $parentSuperAttributes);
    }

    private function getParentProductUrl(Mage_Catalog_Model_Product $childProduct)
    {
        $rewrite = Mage::getModel('core/url_rewrite');
        $params = [
            '_current' => false,
            '_use_rewrite' => true,
            '_secure' => true,
            '_store_to_url' => false,
            '_nosid' => true
        ];
        $arrayOfParentIds = Mage::getSingleton("catalog/product_type_configurable")->getParentIdsByChild($childProduct->getId());
        $parentId = (count($arrayOfParentIds) > 0 ? $arrayOfParentIds[0] : null);
        $url = $childProduct->getProductUrl();
        $rewrite->loadByIdPath('product/' . $parentId);
        $parentUrl = Mage::getUrl($rewrite->getRequestPath(), $params);

        return $parentUrl ? $parentUrl : $url;
    }

    public function formatProductCommonData(Mage_Catalog_Model_Product $product)
    {

        $baseCurrency = Mage::app()->getStore()->getBaseCurrencyCode();
        $storeCurrency = Mage::app()->getStore()->getCurrentCurrency()->getCode();
        $price = $product->getPrice();
        $price_discount = $product->getFinalPrice();

        // Set price from quote currency rate
        if ($baseCurrency !== $storeCurrency) {
            $price = Mage::helper('directory')->currencyConvert($product->getPrice(), $baseCurrency, $storeCurrency);
            $price_discount = Mage::helper('directory')->currencyConvert($product->getFinalPrice(), $baseCurrency,$storeCurrency);
            $product->setPrice($price);
            $product->setFinalPrice($price_discount);
        }

        $return = array(
            'reference' => $product->getSku(),
            'product_link' => $this->helper->productUrlOf($product),
            'image_link' => $this->helper->productImageUrlOf($product),
            'price' => $price,
            'price_discount' => $price_discount,
        );
        // If quote data display quote price
        if ($quoteItem = $product->getData('quoteItem')) {
            $return['price_discount'] = $quoteItem->getPriceInclTax();
            $return['qty'] = $quoteItem->getQty();
        }

        return $return;
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
