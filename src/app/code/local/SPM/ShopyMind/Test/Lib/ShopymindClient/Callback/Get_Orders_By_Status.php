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
                        'description' => null,
                        'qty' => '0.0000',
                        'price' => null,
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
                ),
            ),
        );

        $actual = ShopymindClient_Callback::getOrdersByStatus(
            1,
            '2015-01-01',
            array(array('country' => 'US')),
            0,
            'processing'
        );

        $this->assertEquals($expected, $actual);
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

}
