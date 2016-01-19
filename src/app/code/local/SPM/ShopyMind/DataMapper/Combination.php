<?php

class SPM_ShopyMind_DataMapper_Combination
{
    public function formatSelectedCombinationData(Mage_Catalog_Model_Product $product)
    {
        if ($product->getTypeId() != Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE || !$this->isApplicable($product)) {
            return $product;
        }

        $combinationKey = SPM_ShopyMind_DataMapper_DataTransformer_QuoteItemToProduct::getSelectedCombinationKey();
        $combination = $product->getData($combinationKey);
        $combination->unsetData('is_salable');

        return array(
            'id_combination' => $combination->getId(),
            'combination_name' => $combination->getName(),
            'quantity_remaining' => $combination->getStockItem()->getQty(),
            'active' => (bool) $combination->getIsSalable(),
        );
    }

    public function isApplicable(Mage_Catalog_Model_Product $product)
    {
        return $product->hasData(SPM_ShopyMind_DataMapper_DataTransformer_QuoteItemToProduct::getSelectedCombinationKey());
    }
}
