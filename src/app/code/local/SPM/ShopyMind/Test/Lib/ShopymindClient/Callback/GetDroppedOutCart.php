<?php

/**
 * @loadSharedFixture
 * @doNotIndexAll
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_GetDroppedOutCart extends EcomDev_PHPUnit_Test_Case
{

    public function testGetDroppedOutCartReturnsEmptyResultsWhenNoCart()
    {
        $result = ShopymindClient_Callback::getDroppedOutCart(1000);
        $this->assertEquals(array(), $result);
    }

    public function testGetDroppedOutCartReturnsZeroWhenCountingNoCart()
    {
        $result = ShopymindClient_Callback::getDroppedOutCart(1000, true);
        $this->assertEquals(array('count' => 0), $result);
    }

    /**
     * @loadFixture aDroppedCart
     */
    public function testGetDroppedOutCartFilterCartsByUpdateDate()
    {
        $_11minutesAfterTheOrder = '2014-01-30 13:56:46';
        ShopymindClient_Callback::$now = strtotime($_11minutesAfterTheOrder);
        $resultsWithin10Minutes = ShopymindClient_Callback::getDroppedOutCart(10 * 60, true);
        $resultsWithin12Minutes = ShopymindClient_Callback::getDroppedOutCart(12 * 60, true);
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
        $results = $this->getDroppedOutCartsWithTimeSimulation($_11minutesAfterTheOrder, 10 * 60);

        $expectedResult = array(
            array(
                'sum_cart' => 173.88999999999999,
                'currency' => 'USD',
                'tax_rate' => '1.0000',
                'id_cart' => '1',
                'link_cart' => 'checkout/cart/',
                'articles' => array (
                    array (
                        'id' => 1,
                        'description' => 'LEGGING',
                        'price' => '13.0000',
                        'image_url' => $this->placeholderImageUrl(),
                        'product_url' => 'catalog/product/view/id/1/s/legging/',
                        'id_combination' => false,
                        'qty' => '2.0000',
                        'product_categories' => array(1, 2),
                        'product_manufacturer' => null, // See test below
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
                    'locale' => '_00',
                    'date_last_order' => 0,
                    'nb_order' => '0',
                    'sum_order' => 0,
                    'groups' => array ('1'),
                    'store_id' => '1',
                    'nb_order_year' => '0',
                    'sum_order_year' => 0
                ),
            ),
        );
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
        $results = $this->getDroppedOutCartsWithTimeSimulation($_11minutesAfterTheOrder, 10 * 60);

        $this->assertEquals(42, $results[0]['articles'][0]['product_manufacturer']);
    }

    /**
     * @loadFixture aDroppedCartWithConfigurableProduct
     */
    public function testGetDroppedOutCartReturnsCorrectDataForCartWithConfigurableProduct()
    {
        $_11minutesAfterTheOrder = '2014-01-30 13:56:46';
        $results = $this->getDroppedOutCartsWithTimeSimulation($_11minutesAfterTheOrder, 10 * 60);

        $expectedResult = array(array(
            'id' => 2,
            'description' => 'LEGGING configurable',
            'price' => '13.0000',
            'image_url' => $this->placeholderImageUrl(),
            'product_url' => 'catalog/product/view/id/2/',
            'id_combination' => 1,
            'qty' => '2.0000',
            'product_categories' => array(),
            'product_manufacturer' => null,
        ));
        $this->assertEquals($expectedResult, $results[0]['articles']);
    }

    /**
     * @loadFixture anEmptyDroppedCart
     */
    public function testGetDroppedOutCartFilterEmptyCarts()
    {
        $_11minutesAfterTheOrder = '2014-01-30 13:56:46';
        $results = $this->getDroppedOutCartsWithTimeSimulation($_11minutesAfterTheOrder, 10 * 60);

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
        $results = ShopymindClient_Callback::getDroppedOutCart(10 * 60);
        ShopymindClient_Callback::$now = null;

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
        if (!function_exists('createimagefromjpeg')) {
            $this->markTestSkipped('Impossible to work with jpeg images on your system');
        }
        return '/catalog/product/cache/1/small_image/200x/9df78eab33525d08d6e5fb8d27136e95/images/catalog/product/placeholder/small_image.jpg';
    }

}
