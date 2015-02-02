<?php

/**
 * @loadSharedFixture
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_Get_Missing_Clients extends EcomDev_PHPUnit_Test_Case
{

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        $write = Mage::getSingleton('core/resource')->getConnection('write');
        $write->query(<<<'QUERY'
        TRUNCATE customer_address_entity_int;
        TRUNCATE customer_address_entity_varchar;
        TRUNCATE sales_flat_order_address;
QUERY
        );
    }

    /**
     * @group bugged
     */
    public function testShouldGetCustomersWhoNeverOrdered()
    {
        $this->markTestIncomplete('Bugger method?');
        $expected = array(
            array(
                'customer' => array(
                    'id_customer' => '1',
                    'last_name' => 'Oliver',
                    'first_name' => 'April',
                    'email_address' => 'april.oliver90@example.com',
                    'gender' => '2',
                    'locale' => 'US',
                    'birthday' => '1990-01-01 00:00:00',
                    'optin' => false,
                    'customer_since' => '2014-12-25 10:00:00',
                    'phone1' => '',
                    'phone2' => '',
                    'date_last_order' => 0,
                    'nb_order' => '0',
                    'sum_order' => 0,
                    'groups' => array('1'),
                ),
            ),
        );

        $actual = ShopymindClient_Callback::getMissingClients(
            1,
            date('2014-12-25 10:00:00'),
            array(array('country' => 'US')),
            0
        );

        $this->assertEquals($expected, $actual);
    }

    public function testReturnAnEmptyListIfTheStoreHasNoClients()
    {
        $expected = array(
        );

        $actual = ShopymindClient_Callback::getMissingClients(
            2,
            date('2014-12-25 10:00:00'),
            array(array('country' => 'US')),
            0
        );

        $this->assertEquals($expected, $actual);
    }

}
