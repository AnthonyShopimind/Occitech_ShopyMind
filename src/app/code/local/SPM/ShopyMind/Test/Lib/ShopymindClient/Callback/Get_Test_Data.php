<?php

/**
 * @loadSharedFixture
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_Get_Test_Data extends EcomDev_PHPUnit_Test_Case
{

    public function testCanGetDataWithRandomProducts()
    {
        $testData = ShopymindClient_Callback::getTestData(1, false);

        $this->assertContains('/checkout/cart', $testData['link_cart']);
        foreach ($testData['articles'] as $product) {
        $this->assertRegExp('#/catalog/product/view/id/[1-2]/#', $product['product_url']);
        }
    }

    public function testGetDataOnStoreWithNoProductsReturnEmptyArticles()
    {
        $testData = ShopymindClient_Callback::getTestData(2, false);

        $this->assertEmpty($testData['articles']);
    }

}
