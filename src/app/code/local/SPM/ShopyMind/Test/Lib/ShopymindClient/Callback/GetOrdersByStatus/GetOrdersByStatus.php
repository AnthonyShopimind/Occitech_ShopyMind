<?php
/**
* @loadSharedFixture
*/
class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_GetOrdersByStatus extends EcomDev_PHPUnit_Test_Case
{

    public function testItReturnsFalseWhenNoTimezonesPassed()
    {
        $result = ShopymindClient_Callback::getOrdersByStatus(1, '2015-01-30 17:40:00', array(), 0, 0, false);
        $this->assertFalse($result);
    }

    public function testItReturnsEmptyArrayWhenNoOrdersMatched()
    {
        $result = ShopymindClient_Callback::getOrdersByStatus(1, '2015-01-30 17:40:00', array(), 0, 0, false);
        $this->assertEmpty($result);
    }
}
