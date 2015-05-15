<?php

/**
 * @loadSharedFixture
 * @doNotIndexAll
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_Get_Orders_By_Status extends EcomDev_PHPUnit_Test_Case
{
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

    public function testCanGetProcessingOrders()
    {
        $expected = array(
            array(
                'currency' => 'USD',
                'total_amount' => '100.0000',
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
                    'shop_id_shop' => 1,
                    'nb_order_year' => 1,
                    'sum_order_year' => 0,
                    'region' => null,
                    'postcode' => null,
                ),
                'shipping_number' => array(),
                'date_order' => '2015-01-01 10:00:00'
            ),
        );

        $actual = ShopymindClient_Callback::getOrdersByStatus(
            false,
            '2015-01-01',
            array(array('country' => 'US')),
            0,
            'processing'
        );

        unset($actual[0]['articles']);
        $this->assertEquals($expected, $actual);
    }

    public function testItShouldReturnsProductWithCorrectFormat()
    {
        $expectedProduct = array(
            'id' => '1',
            'description' => 'Produit 1',
            'qty' => 2.0,
            'price' => '13.0000',
            'id_combination' => '1',
            'product_categories' => array(2),
            'product_manufacturer' => null,
            'product_url' => 'catalog/product/view/id/1/',
        );
        $result =  ShopymindClient_Callback::getOrdersByStatus(
            false,
            '2015-01-01',
            array(array('country' => 'US')),
            0,
            'processing'
        );

        unset($result[0]['articles'][0]['image_url']);
        $this->assertEquals($expectedProduct, $result[0]['articles'][0]);
    }

    public function testIfTheStoreHasNoOrderAnEmptyArrayIsReturned()
    {
        $actual = ShopymindClient_Callback::getOrdersByStatus(
            'store-2',
            '2015-01-01',
            array(array('country' => 'US')),
            0,
            'processing'
        );

        $this->assertEmpty($actual);
    }

    public function testItReturnsFalseWhenNoTimezonesPassed()
    {
        $result = ShopymindClient_Callback::getOrdersByStatus(false, '2015-01-30 17:40:00', array(), 0, 0, false);
        $this->assertFalse($result);
    }

    public function testItReturnsEmptyArrayWhenNoOrdersMatched()
    {
        $result = ShopymindClient_Callback::getOrdersByStatus(false, '2015-01-30 17:40:00', array(array('country' => 'US')), 0, 0, false);
        $this->assertEmpty($result);
    }

    public function testItReturnsOrdersCountIfJustCountParameterIsTrue()
    {
        $result = ShopymindClient_Callback::getOrdersByStatus(false, '2015-01-30 17:40:00', array(array('country' => 'US')), 0, 0, true);
        $expected = array('count' => 0);
        $this->assertEquals($expected, $result);
    }

    /**
     * @loadFixture orderWithTrackingNumber
     */
    public function testItShouldReturnsOrderWithShippingNumbers()
    {
        $result = ShopymindClient_Callback::getOrdersByStatus(
            false,
            '2015-01-11',
            array(array('country' => 'US')),
            0,
            'processing'
        );

        $this->assertEquals(array('EXAMPLE0042', 'EXAMPLE0043'), $result[0]['shipping_number']);
    }
}
