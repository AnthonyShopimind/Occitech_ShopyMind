<?php

class SPM_ShopyMind_DataMapper_QuoteItem
{
    public function format(Mage_Sales_Model_Quote_Item $quoteItem)
    {
        $product = $quoteItem->getProduct();
        $combinationId = $this->getCombinationId($quoteItem, $product);
        $qty = $this->getQuantity($quoteItem);

        return array(
            'id_product' => $product->getId(),
            'id_combination' => $combinationId,
            'id_manufacturer' => Mage::helper('shopymind')->manufacturerIdOf($product),
            'price' => $quoteItem->getPriceInclTax(),
            'qty' => $qty
        );
    }

    private function getCombinationId(Mage_Sales_Model_Quote_Item $quoteItem)
    {
        if ($quoteItem instanceof Mage_Sales_Model_Order_Item) {
            $combinationId = $quoteItem->getHasChildren() ? $quoteItem->getProduct()->loadByAttribute('sku', $quoteItem->getSku())->getId() : $quoteItem->getProduct()->getId();
        } else {
            $children = $quoteItem->getChildren();
            $combinationId = count($children) ? $children[0]->getProductId() : $quoteItem->getProduct()->getId();
        }

        return $combinationId;
    }

    private function getQuantity(Mage_Sales_Model_Quote_Item $quoteItem)
    {
        $qty = $quoteItem instanceof Mage_Sales_Model_Order_Item ? $quoteItem->getQtyOrdered() : $quoteItem->getQty();
        return $qty;
    }
}
