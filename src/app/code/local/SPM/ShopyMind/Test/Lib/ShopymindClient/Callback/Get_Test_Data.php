<?php

/**
 * @loadSharedFixture
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_Get_Test_Data extends EcomDev_PHPUnit_Test_Case
{

    protected function setUp()
    {
        parent::setUp();
        $this->replaceByMock('model', 'core/session', $this->guestSession());
    }

    protected function tearDown()
    {
        parent::tearDown();
        Mage::unregister('_resource_singleton/catalog/product_flat');
    }

    public function testCanGetDataWithRandomProducts()
    {
        $testData = ShopymindClient_Callback::getTestData('store-1', false);

        $this->assertContains('checkout/cart', $testData['link_cart']);
        foreach ($testData['articles'] as $product) {
            $this->assertRegExp('#catalog/product/view/id/[1-2]/#', $product['product_link']);
        }
    }

    public function testGetDataOnStoreWithNoProductsReturnEmptyArticles()
    {
        $testData = ShopymindClient_Callback::getTestData('store-2', false);

        $this->assertEmpty($testData['articles']);
    }

}
