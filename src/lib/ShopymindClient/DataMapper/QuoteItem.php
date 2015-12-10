<?php

class ShopymindClient_DataMapper_QuoteItem
{
    public function format(Mage_Sales_Model_Quote_Item $quoteItem)
    {
        $product = $quoteItem->getProduct();
        $children = $quoteItem->getChildren();
        $combinationId = count($children) ? $children[0]->getProductId() : $product->getId();

        return array(
            'id_product' => $product->getId(),
            'id_combination' => $combinationId,
            'id_manufacturer' => $product->getManufacturer(),
            'price' => $quoteItem->getPriceInclTax(),
            'qty' => $quoteItem->getQty(),
        );
    }
}
