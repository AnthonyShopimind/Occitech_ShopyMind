<?php

class SPM_ShopyMind_DataMapper_OrderItem
{
    public function format(Mage_Sales_Model_Order_Item $orderItem)
    {
        $product = $orderItem->getProduct();
        $combinationId = $this->getCombinationId($orderItem, $product);

        return array(
            'id_product' => $product->getId(),
            'id_combination' => $combinationId,
            'id_manufacturer' => Mage::helper('shopymind')->manufacturerIdOf($product),
            'price' => $orderItem->getPriceInclTax(),
            'qty' => $orderItem->getQtyOrdered()
        );
    }

    private function getCombinationId(Mage_Sales_Model_Order_Item $quoteItem)
    {
            $combinationId = $quoteItem->getHasChildren() ? $quoteItem->getProduct()->loadByAttribute('sku', $quoteItem->getSku())->getId() : $quoteItem->getProduct()->getId();

        return $combinationId;
    }
}
