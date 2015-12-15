<?php

/**
 * @group dataMappers
 * @group tdd
 * @loadSharedFixture
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_DataMapper_Product extends EcomDev_PHPUnit_Test_Case
{
    public $SUT;

    public function setup() {
        parent::setup();
        $this->SUT = new SPM_ShopyMind_DataMapper_Product();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        $write = Mage::getSingleton('core/resource')->getConnection('write');
        $write->query(<<<'QUERY'
        TRUNCATE catalog_product_entity_varchar;
        TRUNCATE catalog_product_entity_int;
        TRUNCATE catalog_product_entity_decimal;
        TRUNCATE catalog_product_entity_text;
        TRUNCATE catalog_category_product;
        TRUNCATE catalog_product_entity;
QUERY
        );
    }

    public function testFormattingEmptyProductReturnsNoData()
    {
        $unknownProduct = Mage::getModel('catalog/product')->load(101467987);
        $actual = $this->SUT->format($unknownProduct);
        $this->assertEquals(array(), $actual);
    }

    public function testFormattingSimpleProductReturnsGlobalDataWhenNoScopeDefined()
    {
        $simpleProduct = Mage::getModel('catalog/product')->load(1);
        $actual = $this->SUT->format($simpleProduct);

        $expectedData = array(
            'shop_id_shop' => Mage_Core_Model_App::ADMIN_STORE_ID,
            'id_product' => 1,
            'reference' => 'sku42-pr',
            'lang' => null,
            'name' => 'First simple product',
            'description_short' => 'This is a short description',
            'description' => 'This is a long description',
            'product_link' => 'catalog/product/view/id/1/s/first-simple-product/',
            'image_link' => '/catalog/product/cache/1/small_image/200x200/9df78eab33525d08d6e5fb8d27136e95/m/s/msj000t_1.jpg',
            'combinations' => array(),
            'id_categories' => array(1, 2),
            'id_manufacturer' => null,
            'currency' => 'USD',
            'price' => 13.00,
            'price_discount' => 13.00,
            'quantity_remaining' => 100,
            'date_creation' => '2013-03-05 05:48:12',
            'active' => true,
        );
        $this->assertEquals($expectedData, $actual);
    }

    public function testFormattingSimpleProductReturnsViewDataWhenScopeDefined()
    {
        $this->markTestIncomplete('TODO');
    }

    public function testFormattingProductReturnsCorrectManufacturerIdWhenAvailable()
    {
        $this->markTestIncomplete('TODO');
    }

    public function testFormattingProductReturnsCorrectSpecialPriceWhenProductHasOne()
    {
        $this->markTestIncomplete('TODO');
    }

    public function testFormattingProductReturnsInactiveWhenProductIsNotInStock()
    {
        $this->markTestIncomplete('TODO');
    }
}
