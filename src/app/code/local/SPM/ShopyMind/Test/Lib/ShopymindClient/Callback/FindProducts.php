<?php

if (Mage::getVersion() < 1.6) :
    class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_FindProducts extends PHPUnit_Framework_TestCase
    {
        public function testNothing()
        {
            $this->markTestSkipped(
                'Impossible to run the testsuite on Magento 1.5 since the fixture loader is broken:
                see https://github.com/EcomDev/EcomDev_PHPUnit/pull/229'
            );
        }
    }
else :
/**
 * @loadSharedFixture
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
        $result = ShopymindClient_Callback::findProducts(false, false, '42-pr');
        $this->assertEquals(array($this->aProduct), $result);
    }

    public function testAnEmptyArrayIsReturnedWhenSearchIs2CharsLong()
    {
        $result = ShopymindClient_Callback::findProducts(false, false, 'pr');
        $this->assertEquals(array(), $result);
    }

    /**
     * @loadFixture
     */
    public function testConfigurableProductsHaveCorrectCombinations()
    {
        $result = ShopymindClient_Callback::findProducts(false, false, 'configurable');
        $expectedConfigurableProduct = array(
            'id' => 2,
            'name' => 'First configurable product',
            'combinations' => array(
                array(
                    'id' => 1,
                    'name' => 'First simple product'
                )
            )
        );
        $this->assertEquals(array($expectedConfigurableProduct), $result);
    }

    public function testResultsAreLimitedTo100()
    {
        $this->markTestIncomplete('To be tested and confirmed');
    }

    public function testBundleProductsHaveNoCombinations()
    {
        $this->markTestIncomplete('To be tested and confirmed');
    }

}

endif;
