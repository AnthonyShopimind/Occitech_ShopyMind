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

    private function getCombinationId(Mage_Sales_Model_Order_Item $orderItem)
    {
        $Product = Mage::getModel('catalog/product');
        $combinationId = $orderItem->getHasChildren() ? $Product->getIdBySku($orderItem->getSku()) : $orderItem->getProductId();

        return $combinationId;
    }
}
