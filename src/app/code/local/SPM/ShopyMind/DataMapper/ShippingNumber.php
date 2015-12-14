<?php

class SPM_ShopyMind_DataMapper_ShippingNumber
{
    public function format($orderId)
    {
        return ShopymindClient_Callback::getShippingNumbersForOrderId($orderId);
    }
}
