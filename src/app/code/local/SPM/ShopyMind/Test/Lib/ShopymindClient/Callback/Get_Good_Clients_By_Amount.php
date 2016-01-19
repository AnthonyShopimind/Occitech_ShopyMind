<?php

/**
 * @loadSharedFixture
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_Get_Good_Clients_By_Amount extends EcomDev_PHPUnit_Test_Case
{

    /**
     * @group fixtures-leaking
     */
    public function testGetForADefinedStore()
    {
        $expected = array(
            array(
                'customer' => array(
                    'id_customer' => '2',
                    'last_name' => 'Perez',
                    'first_name' => 'Gary',
                    'email_address' => 'gary.perez41@example.com',
                    'gender' => '1',
                    'locale' => '_00',
                    'birthday' => '1990-01-02 00:00:00',
                    'optin' => false,
                    'customer_since' => '0000-00-00 00:00:00',
                    'phone1' => '',
                    'phone2' => '',
                    'date_last_order' => '2015-12-24 10:00:00',
                    'nb_order' => '2',
                    'sum_order' => '180.00000000',
                    'groups' => array('1'),
                    'shop_id_shop' => '1',
                    'nb_order_year' => '2',
                    'sum_order_year' => '180.00000000',
                    'region' => null,
                    'postcode' => null,
                    'active' => true,
                    'addresses' => array(),
                ),
            ),
        );

        $actual = ShopymindClient_Callback::getGoodClientsByAmount(
            'store-1',
            date('2016-01-01 00:00:00'),
            array(array('country' => 'US')),
            0,
            0,
            10,
            8
        );

        $this->assertEquals($expected, $actual, 'TODO Prevent this test to break when changing year');
    }

}
