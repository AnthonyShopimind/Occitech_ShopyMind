<?php

/**
 * @loadSharedFixture
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_Get_Timezones extends EcomDev_PHPUnit_Test_Case
{

    public function testGetTimezonesGetTimezonesForCustomersInADefinedStore()
    {
        $expected = array(
            array(
                'country_code' => 'US',
                'region_code' => 'GA',
            ),
            array(
                'country_code' => 'US',
                'region_code' => 'NY',
            ),
        );

        $actual = ShopymindClient_Callback::getTimezones('store-1', false);

        $this->assertEquals($expected, $actual);
    }

}
