<?php

/**
 * @loadSharedFixture
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_Get_Products extends EcomDev_PHPUnit_Test_Case
{

    public function testCanGetRandomProducts()
    {
        Mage::app()->setCurrentStore(1);
        $products = ShopymindClient_Callback::getProducts('store-1', false, false, true, 1);

        $this->assertRegExp('#catalog/product/view/id/[1-2]/#', $products[0]['product_url']);

        Mage::app()->setCurrentStore(0);
    }

    public function testIfAStoreHasNoProductsNothingIsReturned()
    {
        $products = ShopymindClient_Callback::getProducts('store-2', false, false, true, 1);

        $this->assertEmpty($products);
    }

}
