<?php

/**
 * @loadSharedFixture
 * @group tdd
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_FindProducts extends EcomDev_PHPUnit_Test_Case
{

    private $aProduct = array(
        'id' => 1,
        'name' => 'First simple product',
        'combinations' => array()
    );

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

    public function testAnEmptyArrayIsReturnedWhenNoProductsMatch()
    {
        $result = ShopymindClient_Callback::findProducts(false, false, 'inexisting text');
        $this->assertEquals(array(), $result);
    }

    public function testResultsCanBeFilteredByName()
    {
        $result = ShopymindClient_Callback::findProducts(false, false, 'First simple');
        $this->assertEquals(array($this->aProduct), $result);
    }

    public function testResultsCanBeFilteredByShop()
    {
        $this->markTestIncomplete('It seems that the product is not filtered properly: error in fixtures?');
        $result = ShopymindClient_Callback::findProducts('website-2', false, 'Premier');

        $expected = array(array_merge($this->aProduct, array(
            'name' => 'Premier super produit'
        )));
        $this->assertEquals($expected, $result);
    }

    /**
     * @loadFixture
     */
    public function testResultsAreOrderedByName()
    {
        $names = array_map(
            function($product) { return $product['name']; },
            ShopymindClient_Callback::findProducts(false, false, 'simple product')
        );

        $expectedNames = array(
            'Another simple product',
            'First simple product',
        );
        $this->assertEquals($expectedNames, $names);
    }

    /**
     * @loadFixture
     */
    public function testOnlyActiveProductsAreReturned()
    {
        $result = ShopymindClient_Callback::findProducts(false, false, 'simple product');
        $this->assertEquals(array($this->aProduct), $result);
    }

    public function testResultsCanBeFilteredBySKU()
    {
        $this->markTestIncomplete();
    }

    public function testAnEmptyArrayIsReturnedWhenSearchIs2CharsLong()
    {
        $this->markTestIncomplete();
    }

    public function testSimpleProductsDoNotHaveCombinations()
    {
        $this->markTestIncomplete();
    }

    public function testConfigurableProductsHaveCorrectCombinations()
    {
        $this->markTestIncomplete();
    }

    public function testResultsAreLimitedTo10()
    {
        $this->markTestIncomplete('Check if needed and the max size');
    }

    public function testBundleProductsHaveNoCombinations()
    {
        $this->markTestIncomplete('?? TODO ??');
    }

}
