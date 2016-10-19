<?php

class SPM_ShopyMind_DataMapper_Order
{
    public function format(Mage_Sales_Model_Order $order)
    {
        $scope = SPM_ShopyMind_Model_Scope::fromOrder($order);
        $customer = $this->customerFor($order);
        $shippingNumber = $this->shippingNumberFor($order);

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
            'currency_rate' => $order->getBaseToOrderRate(),
            'tax_rate' => $order->getBaseToOrderRate(),
            'currency' => $order->getOrderCurrencyCode(),
            'date_order' => $order->getCreatedAt(),
            'voucher_used' => $this->getVoucherFor($order),
            'voucher_amount' => $order->getDiscountAmount(),
            'products' => $this->productsFor($order),
            'customer' => $customer,
            'shipping_number' => $shippingNumber,
            'amount_without_tax' => $order->getBaseSubtotal(),
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
        $cartId = $order->getQuoteId();
        $cart = Mage::getModel('sales/quote')->load($cartId);
        if (!$cart) {
            return false;
        }

        return ($cart->getUpdatedAt() !== null && $cart->getUpdatedAt() !== '') ? $cart->getUpdatedAt() : $order->getCreatedAt();
    }

    private function productsFor($order)
    {
        $ItemFormatter = new SPM_ShopyMind_DataMapper_OrderItem();
        return array_map(
            array($ItemFormatter, 'format'),
            $order->getAllVisibleItems()
        );
    }

    private function customerFor($order)
    {
        $customerIdentifier = $order->getCustomerId() ? $order->getCustomerId() : $order->getCustomerEmail();
        $GetUser = new SPM_ShopyMind_Action_GetUser($customerIdentifier);

        return $GetUser->process();
    }

    private function shippingNumberFor($order)
    {
        $ShippingNumberDataMapper = new SPM_ShopyMind_DataMapper_ShippingNumber();

        return $ShippingNumberDataMapper->format($order->getId());
    }
}
