<?php

class SPM_ShopyMind_DataMapper_Customer
{
    public function format($customerId)
    {
        return ShopymindClient_Callback::getUser($customerId);
    }
}
