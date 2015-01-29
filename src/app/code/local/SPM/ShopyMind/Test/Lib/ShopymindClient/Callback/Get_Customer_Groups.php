<?php

class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_Get_Customer_Groups extends EcomDev_PHPUnit_Test_Case
{

    public function testCanGetCustomerGroups()
    {
        $expected = array(
            'NOT LOGGED IN',
            'General',
            'Wholesale',
            'Retailer',
        );

        $actual = ShopymindClient_Callback::getCustomerGroups(1);

        $this->assertEquals($expected, $actual);
    }

}
