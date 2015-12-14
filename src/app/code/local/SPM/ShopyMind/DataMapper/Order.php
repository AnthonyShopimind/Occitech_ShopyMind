<?php

class SPM_ShopyMind_DataMapper_Order
{
    public function format(Mage_Sales_Model_Order $order, $customer, $shippingNumber)
    {
        $scope = SPM_ShopyMind_Model_Scope::fromOrder($order);

        return array(
            'shop_id_shop' => $scope->getId(),
            'lang' => $scope->getLang(),
            'order_is_confirm' => $this->isConfirmed($order),
            'order_reference' => $order->getIncrementId(),
            'id_cart' => $order->getQuoteId(),
            'id_status' => $order->getStatus(),
            'date_cart' => $this->cartDateFor($order),
            'id_order' => $order->getId(),
            'amount' => $order->getBaseGrandTotal(),
            'tax_rate' => $order->getBaseToOrderRate(),
            'currency' => $order->getOrderCurrencyCode(),
            'date_order' => $order->getCreatedAt(),
            'voucher_used' => $this->getVoucherFor($order),
            'voucher_amount' => $order->getDiscountAmount(),
            'products' => $this->productsFor($order),
            'customer' => $customer,
            'shipping_number' => $shippingNumber,
        );
    }

    private function isConfirmed($order)
    {
        return ($order->getState() == Mage_Sales_Model_Order::STATE_PROCESSING || $order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE) ? true : false;
    }

    private function getVoucherFor($order)
    {
        $voucherUsed = array ();
        $vouchersOrder = $order->getCouponCode();
        if ($vouchersOrder) {
            $voucherUsed[] = $vouchersOrder;
        }

        return $voucherUsed;
    }

    private function cartDateFor($order)
    {
        $cart = $order->getQuote();
        return ($cart->getUpdatedAt() !== null && $cart->getUpdatedAt() !== '') ? $cart->getUpdatedAt() : $order->getCreatedAt();
    }

    private function productsFor($order)
    {
        $ItemFormatter = new SPM_ShopyMind_DataMapper_QuoteItem();
        return array_map(
            array($ItemFormatter, 'format'),
            $order->getQuote()->getAllVisibleItems()
        );
    }
}
