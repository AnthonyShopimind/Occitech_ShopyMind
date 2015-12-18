<?php

/**
 * @loadSharedFixture
 * @group 54
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_Get_Products extends EcomDev_PHPUnit_Test_Case
{
    protected function tearDown()
    {
        parent::tearDown();
        Mage::unregister('_resource_singleton/catalog/product_flat');

        if (session_id()) {
            session_destroy();
        }
    }

    public function testCanGetRandomProducts()
    {
        $products = ShopymindClient_Callback::getProducts('store-1', false, false, true, 1);

        $this->assertRegExp('#catalog/product/view/id/[1-2]/#', $products[0]['product_link']);
    }

    public function testIfAStoreHasNoProductsNothingIsReturned()
    {
        $products = ShopymindClient_Callback::getProducts('store-2', false, false, true, 1);

        $this->assertEmpty($products);
    }

    public function testCanGetRandomProductsIfNoStoreIsDefined()
    {
        $products = ShopymindClient_Callback::getProducts(null, false, false, true, 1);

        $this->assertRegExp('#catalog/product/view/id/[1-2]/#', $products[0]['product_link']);
    }

    public function testCanGetRandomProductsInSpecificsProductsIds()
    {
        $products = ShopymindClient_Callback::getProducts(null, false, array(1), true, 1);

        $this->assertRegExp('#catalog/product/view/id/1/#', $products[0]['product_link']);
    }
}
