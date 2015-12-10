<?php

class ShopymindClient_DataMapper_Order
{

    public function format(Mage_Sales_Model_Order $order, $customer, $shippingNumber)
    {
        $ProductItemFormatter = new ShopymindClient_DataMapper_QuoteItem();
        $cart = $order->getQuote();
        $productItems = $cart->getAllVisibleItems();

        $products = array();
        foreach($productItems as $productItem) {
            $products[] = $ProductItemFormatter->format($productItem);
        }

        $voucherUsed = array ();
        $vouchersOrder = $order->getCouponCode();
        if ($vouchersOrder) {
            $voucherUsed [] = $vouchersOrder;
        }

        $dateCart = ($cart->getUpdatedAt() !== null && $cart->getUpdatedAt() !== '') ? $cart->getUpdatedAt() : $order->getCreatedAt();
        $state = ($order->getState() == Mage_Sales_Model_Order::STATE_PROCESSING || $order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE) ? true : false;

        return array(
            'shop_id_shop' => $cart->getStoreId(),
            'order_is_confirm' => $state,
            'order_reference' => '',
            'id_cart' => $cart->getId(),
            'id_status' => $order->getStatus(),
            'date_cart' => $dateCart,
            'id_order' => $order->getIncrementId(),
            'lang' => '',
            'amount' => $order->getBaseGrandTotal(),
            'tax_rate' => $order->getBaseToOrderRate(),
            'currency' => $order->getOrderCurrencyCode(),
            'date_order' => $order->getCreatedAt(),
            'voucher_used' => $voucherUsed,
            'voucher_amount' => $order->getDiscountAmount(),
            'products' => $products,
            'customer' => $customer,
            'shipping_number' => $shippingNumber,
        );
    }
}
