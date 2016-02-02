<?php

class SPM_ShopyMind_DataMapper_QuoteItem
{
    public function format(Mage_Sales_Model_Quote_Item $quoteItem)
    {
        $product = $quoteItem->getProduct();
        $combinationId = $this->getCombinationId($quoteItem);

        return array(
            'id_product' => $product->getId(),
            'id_combination' => $combinationId,
            'id_manufacturer' => Mage::helper('shopymind')->manufacturerIdOf($product),
            'price' => $quoteItem->getPriceInclTax(),
            'qty' => $quoteItem->getQty()
        );
    }

    private function getCombinationId(Mage_Sales_Model_Quote_Item $quoteItem)
    {
            $children = $quoteItem->getChildren();
            $combinationId = count($children) ? $children[0]->getProductId() : $quoteItem->getProductId();

        return $combinationId;
    }

}
