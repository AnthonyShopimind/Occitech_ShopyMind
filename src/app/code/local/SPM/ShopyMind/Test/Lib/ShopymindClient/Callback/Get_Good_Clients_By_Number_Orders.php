<?php

/**
 * @loadSharedFixture
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_Get_Good_Clients_By_Number_Orders extends EcomDev_PHPUnit_Test_Case
{

    /**
     * @group fixtures-leaking
     */
    public function testGetForADefinedStore()
    {
        $expected = array(
            array(
                'customer' => array(
                    'id_customer' => '1',
                    'last_name' => 'Oliver',
                    'first_name' => 'April',
                    'email' => 'april.oliver90@example.com',
                    'gender' => '2',
                    'locale' => '_00',
                    'birthday' => '1990-01-01',
                    'optin' => false,
                    'newsletter' => false,
                    'customer_since' => '0000-00-00 00:00:00',
                    'phone1' => '',
                    'phone2' => '',
                    'date_last_order' => '2015-12-24 10:00:00',
                    'nb_order' => '3',
                    'sum_order' => 0,
                    'groups' => array('1'),
                    'shop_id_shop' => '1',
                    'nb_order_year' => '3',
                    'sum_order_year' => 0,
                    'active' => true,
                    'addresses' => array(),
                ),
            ),
        );

        $actual = ShopymindClient_Callback::getGoodClientsByNumberOrders(
            'store-1',
            date('2016-01-01 00:00:00'),
            array(array('country' => 'US')),
            2,
            0,
            10,
            8
        );

        $this->assertEquals($expected, $actual, 'TODO Prevent this test to break when changing year');
    }

}
