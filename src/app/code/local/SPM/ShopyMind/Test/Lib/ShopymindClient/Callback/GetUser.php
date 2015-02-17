<?php

/**
 * @loadSharedFixture
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_GetUser extends EcomDev_PHPUnit_Test_Case
{

    private $aCustomer = array(
        'id_customer'     => 1,
        'shop_id_shop'    => 1,
        'optin'           => false,
        'customer_since'  => '2014-10-21 14:10:59',
        'last_name'       => 'Oliver',
        'first_name'      => 'April',
        'email_address'   => 'april.oliver90@example.com',
        'phone1'          => '',
        'phone2'          => '',
        'gender'          => 2,
        'birthday'        => 0,
        'locale'          => '_00',
        'date_last_order' => 0,
        'nb_order'        => 0,
        'sum_order'       => 0,
        'nb_order_year'   => 0,
        'sum_order_year'  => 0,
        'groups'          => array(1)
    );

    public function testItReturnsCorrectUserInformationsWhenCustomerHasNotPassedAnyOrder()
    {
        $result = ShopymindClient_Callback::getUser(1);
        $this->assertEquals($this->aCustomer, $result);
    }

    /**
     * @loadFixture userSubscribedToNewsletter
     */
    public function testItReturnsOptinWhenCustomerHasSubscribedToTheNewsletter()
    {
        $result = ShopymindClient_Callback::getUser(1);
        $this->assertTrue($result['optin']);
    }

    /**
     * @loadFixture userPassedSeveralOrders
     */
    public function testItReturnsCorrectOrderStatsWhenCustomerPassedSeveralOrders()
    {
        $result = ShopymindClient_Callback::getUser(1);
        $expectedOrderStats = array(
            'nb_order' => 2,
            'sum_order' => (146.35 + 14.00),
            'nb_order_year' => 1,
            'sum_order_year' => 14.00,
        );
        $this->assertEquals($expectedOrderStats, array_intersect_key($result, $expectedOrderStats));
    }

    /**
     * @loadFixture anotherUserWithOrders
     */
    public function testItAcceptsSeveralCustomerEmails()
    {
        $result = ShopymindClient_Callback::getUser(array(
            'jane.doe34@example.com',
            'april.oliver90@example.com'
        ));
        $this->assertCount(2, $result);
    }

    /**
     * @loadFixture anotherUserWithOrders
     */
    public function testItReturnsOnlyEmailAddressesFound()
    {
        $result = ShopymindClient_Callback::getUser(array(
            'jane.doe34@example.com',
            'april.oliver90@example.com',
            'unknown-email@example.com',
        ));
        $this->assertCount(2, $result);
    }

    /**
     * @loadFixture anotherUserWithOrders
     * @group bugged?
     */
    public function testItDoesNotReturnOrderStatsWhenSearchingByCustomerEmails()
    {
        $results = ShopymindClient_Callback::getUser(array(
            'jane.doe34@example.com',
            'april.oliver90@example.com'
        ));

        $noOrderStats = array(
            'nb_order' => 0,
            'sum_order' => 0,
            'nb_order_year' => 0,
            'sum_order_year' => 0,
        );

        $results = array_map(function($result) use ($noOrderStats) {
            return array_intersect_key($result, $noOrderStats);
        }, $results);
        $this->assertEquals($noOrderStats, $results[0]);
        $this->assertEquals($noOrderStats, $results[1]);
    }

    /**
     * @loadFixture anotherUserWithOrders
     */
    public function testItReturnsCorrectCustomerDataWhenSearchingInOrdersBillingAddresses()
    {
        $result = ShopymindClient_Callback::getUser(array(
            'jane.doe34@example.com',
            'april.oliver90@example.com'
        ));
        $expectedData = array(
            array_merge($this->aCustomer, array(
                'id_customer' => 'jane.doe34@example.com',
                'shop_id_shop' => 2,
                'customer_since' => '2015-01-21 14:14:04',
                'last_name' => 'Doe',
                'first_name' => 'Jane',
                'email_address' => 'jane.doe34@example.com',
                'phone1' => '0102030455',
                'gender' => 1,
                'locale' => 'FR',
                'groups' => array(1)
            )),
            array_merge($this->aCustomer, array(
                'id_customer' => 'april.oliver90@example.com',
                'customer_since' => '2014-02-06 14:14:04',
                'phone1' => '0102030405',
                'gender' => 2,
                'birthday' => '1962-08-29 00:00:00',
                'locale' => 'FR',
                'groups' => array(1)
            ))
        );
        $this->assertEquals($expectedData, $result);
    }

    /**
     * @loadFixture anotherUserWithQuotes
     */
    public function testItReturnsCorrectCustomerDataWhenSearchingInQuotes()
    {
        $result = ShopymindClient_Callback::getUser(array(
            'jane.doe34@example.com',
            'april.oliver90@example.com'
        ), true);
        $expectedData = array(
            array_merge($this->aCustomer, array(
                'id_customer' => 'jane.doe34@example.com',
                'shop_id_shop' => 2,
                'customer_since' => '2015-01-21 14:14:04',
                'last_name' => 'Doe',
                'first_name' => 'Jane',
                'email_address' => 'jane.doe34@example.com',
                'phone1' => '0102030455',
                'gender' => 1,
                'locale' => 'FR',
                'groups' => array(1)
            )),
            array_merge($this->aCustomer, array(
                'id_customer' => 'april.oliver90@example.com',
                'customer_since' => '2014-02-06 14:14:04',
                'phone1' => '0102030405',
                'gender' => 2,
                'birthday' => '1962-08-29 00:00:00',
                'locale' => 'FR',
                'groups' => array(1)
            ))
        );
        $this->assertEquals($expectedData, $result);
    }
}
