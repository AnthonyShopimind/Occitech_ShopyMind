<?php

class SPM_ShopyMind_DataMapper_DataTransformer_QuoteItemToProduct
{
    const SELECTED_COMBINATION = 'selected_combination';

    public function transform(Mage_Sales_Model_Quote_Item $quoteItem)
    {
        $combinations = $quoteItem->getChildren();
        $product = $quoteItem->getProduct();
        $product->setData('quoteItem',$quoteItem);
        if (count($combinations)) {
           $product = $this->enrichWithAssociatedCombination($product, $combinations[0]);
        }

        return $product;
    }

    public function enrichWithAssociatedCombination(Mage_Catalog_Model_Product $product, Mage_Sales_Model_Quote_Item $combination)
    {
        $Product = Mage::getModel('catalog/product');
        $product->setData(self::SELECTED_COMBINATION, $Product->load($combination->getProductId()));
        return $product;
    }

    public static function getSelectedCombinationKey()
    {
        return self::SELECTED_COMBINATION;
    }
}
