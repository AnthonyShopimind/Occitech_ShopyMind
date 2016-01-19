<?php

/**
 * @loadSharedFixture
 * @doNotIndexAll
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_GetDroppedOutCart extends EcomDev_PHPUnit_Test_Case
{
    protected function setUp()
    {
        parent::setUp();
        $this->replaceByMock('model', 'core/session', $this->guestSession());
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

    public function testGetDroppedOutCartReturnsEmptyResultsWhenNoCart()
    {
        $result = ShopymindClient_Callback::getDroppedOutCart('store-1', 1000, 1000);
        $this->assertEquals(array(), $result);
    }

    public function testGetDroppedOutCartReturnsZeroWhenCountingNoCart()
    {
        $result = ShopymindClient_Callback::getDroppedOutCart('store-1', 1000, 1000, true);
        $this->assertEquals(array('count' => 0), $result);
    }

    /**
     * @loadFixture aDroppedCart
     */
    public function testGetDroppedOutCartFilterCartsByUpdateDate()
    {
        $_11minutesAfterTheOrder = '2014-01-30 13:56:46';
        ShopymindClient_Callback::$now = strtotime($_11minutesAfterTheOrder);
        $resultsWithin10Minutes = ShopymindClient_Callback::getDroppedOutCart('store-1', 10 * 60, 10 * 60, true);
        $resultsWithin12Minutes = ShopymindClient_Callback::getDroppedOutCart('store-1', 12 * 60, 10 * 60, true);
        ShopymindClient_Callback::$now = null;

        $this->assertEquals(1, $resultsWithin10Minutes['count']);
        $this->assertEquals(0, $resultsWithin12Minutes['count']);
    }

    /**
     * @loadFixture aDroppedCart
     */
    public function testGetDroppedOutCartReturnsCorrectDataForCartWithSimpleProduct()
    {
        $_11minutesAfterTheOrder = '2014-01-30 13:56:46';
        $results = $this->getDroppedOutCartsWithTimeSimulation($_11minutesAfterTheOrder, 'store-1', 10 * 60, 10 * 60);

        $expectedResult = array(
            array(
                'sum_cart' => 173.88999999999999,
                'currency' => 'USD',
                'tax_rate' => '1.0000',
                'id_cart' => '1',
                'link_cart' => 'checkout/cart/',
                'date_cart' => '2014-01-29 12:45:46',
                'products' => array (
                    array (
                        'id_product' => 125,
                        'id_manufacturer' => null,
                        'price' => '13.0000',
                        'reference' => '14156575-XS-9394',
                        'combinations' => array(),
                        'product_link' => 'catalog/product/view/id/125/s/legging/',
                        'image_link' => 'media/catalog/product/cache/1/small_image/200x200/9df78eab33525d08d6e5fb8d27136e95/images/catalog/product/placeholder/small_image.jpg',
                        'price_discount' => '13.0000',
                        'name' => 'LEGGING',
                        'description_short' => null,
                        'description' => null,
                        'id_categories' => array(1, 2),
                        'date_creation' => '1970-01-01 00:00:00',
                        'active' => true,
                        'quantity_remaining' => 4.000,
                        'shop_id_shop' => '1',
                        'lang' => 'en',
                        'currency' => 'USD',
                    ),
                ),
                'customer' => array(
                    'id_customer' => '1234',
                    'optin' => false,
                    'customer_since' => '0000-00-00 00:00:00',
                    'last_name' => 'Oliver',
                    'first_name' => 'April',
                    'email_address' => 'april.oliver90@example.com',
                    'phone1' => '',
                    'phone2' => '',
                    'gender' => '2',
                    'birthday' => 0,
                    'locale' => 'en_00',
                    'date_last_order' => 0,
                    'nb_order' => '0',
                    'sum_order' => 0,
                    'groups' => array ('1'),
                    'shop_id_shop' => '1',
                    'nb_order_year' => '0',
                    'sum_order_year' => 0,
                    'region' => null,
                    'postcode' => null,
                    'active' => true,
                    'addresses' => array(),
                ),
            ),
        );
        unset($expectedResult[0]['products'][0]['image_link'], $results[0]['products'][0]['image_link']);
        $this->assertEquals($expectedResult, $results);
    }

    /**
     * @loadFixture aDroppedCart
     */
    public function testGetDroppedOutCartReturnsCorrectManufacturer()
    {
        $this->markTestIncomplete(
            'TODO: make the test work, because for some reasons the mock model is not loaded when using' .
            'Mage::getModel("sales/quote")->load($cartId)->getAllVisibleItems() to load products'
        );
        $product = $this->getModelMock('catalog/product', array('getManufacturer'));
        $product->expects($this->any())
            ->method('getManufacturer')
            ->will($this->returnValue(42));
        $this->replaceByMock('model', 'catalog/product', $product);

        $_11minutesAfterTheOrder = '2014-01-30 13:56:46';
        $results = $this->getDroppedOutCartsWithTimeSimulation($_11minutesAfterTheOrder, 'store-1', 10 * 60, 10 * 60);

        $this->assertEquals(42, $results[0]['products'][0]['product_manufacturer']);
    }

    /**
     * @loadFixture aDroppedCartWithConfigurableProduct
     */
    public function testGetDroppedOutCartReturnsCorrectDataForCartWithConfigurableProduct()
    {
        $_11minutesAfterTheOrder = '2014-01-30 13:56:46';
        $results = $this->getDroppedOutCartsWithTimeSimulation($_11minutesAfterTheOrder, 'store-1', 10 * 60, 10 * 60);

        $expectedResult = array(array(
            'id_product' => 2,
            'id_combination' => 125,
            'combination_name' => 'LEGGING',
            'id_manufacturer' => null,
            'price' => '13.0000',
            'quantity_remaining' => '4.0000',
            'reference' => '14156575-XS',
            'product_link' => 'catalog/product/view/id/2/',
            'image_link' => '/catalog/product/cache/1/smal...ge.jpg',
            'price_discount' => '13.0000',
            'name' => 'LEGGING configurable',
            'description_short' => null,
            'description' => null,
            'id_categories' => array(2),
            'date_creation' => '1970-01-01 00:00:00',
            'active' => true,
            'shop_id_shop' => '1',
            'lang' => 'en',
            'currency' => 'USD',
        ));

        unset($expectedResult[0]['image_link'], $results[0]['products'][0]['image_link']);
        $this->assertEquals($expectedResult, $results[0]['products']);
    }

    /**
     * @loadFixture anEmptyDroppedCart
     */
    public function testGetDroppedOutCartFilterEmptyCarts()
    {
        $_11minutesAfterTheOrder = '2014-01-30 13:56:46';
        $results = $this->getDroppedOutCartsWithTimeSimulation($_11minutesAfterTheOrder, 'store-1', 10 * 60, 10 * 60);

        $this->assertEmpty($results);
    }

    /**
     * @loadFixture aDroppedCart
     * @loadFixture anOrderedCart
     */
    public function testGetDroppedOutCartFilterCartsOfCustomersWhoOrderedWithinTheLast7Days()
    {
        $_11minutesAfterTheOrder = '2014-01-30 13:56:46';
        ShopymindClient_Callback::$now = strtotime($_11minutesAfterTheOrder);
        $results = ShopymindClient_Callback::getDroppedOutCart('store-1', 10 * 60, 10 * 60);
        ShopymindClient_Callback::$now = null;

        $this->assertEmpty($results);
    }

    public function testIfAStoreHasNotCartTheListIsEmpty()
    {
        $results = ShopymindClient_Callback::getDroppedOutCart('store-2', 10 * 60, 10 * 60);
        $this->assertEmpty($results);
    }

    private function getDroppedOutCartsWithTimeSimulation($simulatedTime)
    {
        $params = array_slice(func_get_args(), 1);

        ShopymindClient_Callback::$now = strtotime($simulatedTime);
        $results = call_user_func_array(
            array('ShopymindClient_Callback', 'getDroppedOutCart'),
            $params
        );
        ShopymindClient_Callback::$now = null;
        return $results;
    }

    private function placeholderImageUrl()
    {
        if (!function_exists('imagecreatefromjpeg')) {
            $this->markTestSkipped('Impossible to work with jpeg images on your system');
        }
        return '/catalog/product/cache/1/small_image/200x/9df78eab33525d08d6e5fb8d27136e95/images/catalog/product/placeholder/small_image.jpg';
    }

}
