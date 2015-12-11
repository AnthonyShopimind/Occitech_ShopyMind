<?php

class SPM_ShopyMind_DataMapper_Order
{
    public function format(Mage_Sales_Model_Order $order, $customer, $shippingNumber)
    {
        $ProductItemFormatter = new SPM_ShopyMind_DataMapper_QuoteItem();
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

        $scope = SPM_ShopyMind_Model_Scope::fromOrder($order);

        return array(
            'shop_id_shop' => $scope->getId(),
            'lang' => $scope->getLang(),
            'order_is_confirm' => $state,
            'order_reference' => $order->getIncrementId(),
            'id_cart' => $cart->getId(),
            'id_status' => $order->getStatus(),
            'date_cart' => $dateCart,
            'id_order' => $order->getId(),
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
