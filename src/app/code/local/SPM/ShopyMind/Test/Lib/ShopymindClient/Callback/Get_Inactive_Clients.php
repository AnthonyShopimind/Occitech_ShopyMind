<?php

/**
 * @loadSharedFixture
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_Get_Inactive_Clients extends EcomDev_PHPUnit_Test_Case
{

    public function testIfNoTimezonesAreGivenReturnFalse()
    {
        $this->assertFalse(ShopymindClient_Callback::getInactiveClients(1, '2015-01-31', array(), 3));
    }

    public function testCanGetCustomersWithoutOrdersSinceThreeMonths()
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
                    'date_last_order' => 0,
                    'nb_order' => '0',
                    'sum_order' => 0,
                    'groups' => array('1'),
                    'store_id' => '1',
                    'nb_order_year' => '0',
                    'sum_order_year' => 0,
                ),
            ),
        );

        $actual = ShopymindClient_Callback::getInactiveClients(1, '2015-01-31 23:59:59', array(array('country' => 'US', 'region' => 'AL')), 3);

        $this->assertEquals($expected, $actual);
    }

    public function testIfACountryHasNoOrderTheListIsEmpty()
    {
        $actual = ShopymindClient_Callback::getInactiveClients(1, '2015-01-31 23:59:59', array(array('country' => 'FR')), 3);

        $this->assertEmpty($actual);
    }

    public function testIfAStoreHasNoOrderTheListIsEmpty()
    {
        $actual = ShopymindClient_Callback::getInactiveClients(2, '2015-01-31 23:59:59', array(array('country' => 'US')), 3);

        $this->assertEmpty($actual);
    }

}
