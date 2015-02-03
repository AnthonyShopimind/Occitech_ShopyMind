<?php

class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_GetShops extends EcomDev_PHPUnit_Test_Case
{

    /**
     * @loadFixture singleStore
     */
    public function testItReturnsEmptyArrayIfNotAMultiStore()
    {
        $result = ShopymindClient_Callback::getShops();
        $this->assertEmpty($result);
    }

    /**
     * @loadFixture multiStore
     */
    public function testItReturnsArrayWithStoreIdAsKeyAndStoreNameAsValue()
    {
        $result = ShopymindClient_Callback::getShops();
        $expected = array(
            1 => 'France B2C',
            2 => 'France B2B'
         );
        $this->assertEquals($expected, $result);
    }
}
