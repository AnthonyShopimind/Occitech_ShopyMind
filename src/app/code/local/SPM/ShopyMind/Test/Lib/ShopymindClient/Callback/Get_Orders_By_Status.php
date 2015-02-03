<?php

/**
 * @loadSharedFixture
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_Get_Orders_By_Status extends EcomDev_PHPUnit_Test_Case
{

    public function testCanGetProcessingOrders()
    {
        $expected = array(
            array(
                'currency' => 'EUR',
                'total_amount' => '100.0000',
                'articles' => array(
                    array(
                        'id' => '1',
                        'description' => 'Produit 1',
                        'qty' => '2.0000',
                        'price' => 13.00,
                        'id_combination' => false,
                        'product_categories' => array(2),
                        'product_manufacturer' => null,
                        'image_url' => '/catalog/product/cache/1/small_image/200x200/9df78eab33525d08d6e5fb8d27136e95/images/catalog/product/placeholder/small_image.jpg',
                        'product_url' => 'catalog/product/view/id/1/',
                    ),
                ),
                'id_order' => '2',
                'customer' => array(
                    'id_customer' => '1',
                    'optin' => false,
                    'customer_since' => '0000-00-00 00:00:00',
                    'last_name' => 'Oliver',
                    'first_name' => 'April',
                    'email_address' => 'april.oliver90@example.com',
                    'phone1' => '',
                    'phone2' => '',
                    'gender' => '2',
                    'birthday' => '1990-01-01 00:00:00',
                    'locale' => '_00',
                    'date_last_order' => '2015-01-01 10:00:00',
                    'nb_order' => '1',
                    'sum_order' => 0,
                    'groups' => array('1'),
                    'store_id' => 1,
                    'nb_order_year' => 1,
                    'sum_order_year' => 0
                ),
                'shipping_number' => array(),
                'date_order' => '2015-01-01 10:00:00'
            ),
        );

        $actual = ShopymindClient_Callback::getOrdersByStatus(
            1,
            '2015-01-01',
            array(array('country' => 'US')),
            0,
            'processing'
        );

        unset($actual[0]['articles'], $expected[0]['articles']);
        $this->assertEquals($expected, $actual);
    }

    public function testItShouldReturnsProductWithCorrectFormat()
    {
        $expectedProduct = array(
            'id' => '1',
            'description' => 'Produit 1',
            'qty' => '2.0000',
            'price' => 13.00,
            'id_combination' => false,
            'product_categories' => array(2),
            'product_manufacturer' => null,
            'image_url' => '/catalog/product/cache/1/small_image/200x200/9df78eab33525d08d6e5fb8d27136e95/images/catalog/product/placeholder/small_image.jpg',
            'product_url' => 'catalog/product/view/id/1/',
        );
        $result =  ShopymindClient_Callback::getOrdersByStatus(
            1,
            '2015-01-01',
            array(array('country' => 'US')),
            0,
            'processing'
        );

        $this->assertEquals($expectedProduct, $result[0]['articles'][0]);
    }

    public function testIfTheStoreHasNoOrderAnEmptyArrayIsReturned()
    {
        $actual = ShopymindClient_Callback::getOrdersByStatus(
            2,
            '2015-01-01',
            array(array('country' => 'US')),
            0,
            'processing'
        );

        $this->assertEmpty($actual);
    }

    public function testItReturnsFalseWhenNoTimezonesPassed()
    {
        $result = ShopymindClient_Callback::getOrdersByStatus(1, '2015-01-30 17:40:00', array(), 0, 0, false);
        $this->assertFalse($result);
    }

    public function testItReturnsEmptyArrayWhenNoOrdersMatched()
    {
        $result = ShopymindClient_Callback::getOrdersByStatus(1, '2015-01-30 17:40:00', array(), 0, 0, false);
        $this->assertEmpty($result);
    }

    public function testItReturnsOrdersCountIfJustCountParameterIsTrue()
    {
        $result = ShopymindClient_Callback::getOrdersByStatus(1, '2015-01-30 17:40:00', array(array('country' => 'US')), 0, 0, true);
        $expected = array('count' => 0);
        $this->assertEquals($expected, $result);
    }

    /**
     * @loadFixture orderWithTrackingNumber
     */
    public function testItShouldReturnsOrderWithShippingNumber()
    {
        $result = ShopymindClient_Callback::getOrdersByStatus(
            1,
            '2015-01-11',
            array(array('country' => 'US')),
            0,
            'processing'
        );

        $this->assertEquals(array('EXAMPLE0042', 'EXAMPLE0043'), $result[0]['shipping_number']);
    }
}
