<?php

/**
 * @loadSharedFixture
 * @doNotIndexAll
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_Get_Products extends EcomDev_PHPUnit_Test_Case
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

    public function testProductsHaveCorrectDataWithCommonFormatAndScopeInformation()
    {
        $products = ShopymindClient_Callback::getProducts('store-1', false, array(1), true, 1);
        $firstProduct = $products[0];
        $firstProductKeys = array_keys($firstProduct);

        // ensures that correct formatters are used implicitly, to be improved when extracted into a cleaner action with injected dependencies
        $expectedProductCommonFormatKeys = array(
            'shop_id_shop',
            'id_product',
            'reference',
            'lang',
            'name',
            'description_short',
            'description',
            'product_link',
            'image_link',
            'combinations',
            'id_categories',
            'id_manufacturer',
            'currency',
            'price',
            'price_discount',
            'quantity_remaining',
            'date_creation',
            'active',
        );
        sort($firstProductKeys);
        sort($expectedProductCommonFormatKeys);

        $this->assertEquals($expectedProductCommonFormatKeys, $firstProductKeys);
        $this->assertEquals('Produit 1', $firstProduct['name']);
        $this->assertEquals(100.0, $firstProduct['quantity_remaining']);
        $this->assertEquals(1, $firstProduct['shop_id_shop']);
    }

}
