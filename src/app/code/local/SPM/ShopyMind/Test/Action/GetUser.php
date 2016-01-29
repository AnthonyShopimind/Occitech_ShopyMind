<?php

/**
 * @loadSharedFixture
 * @group actions
 * @group customers
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_GetUser extends EcomDev_PHPUnit_Test_Case
{

    private $aCustomer = array(
        'id_customer' => 1,
        'shop_id_shop' => 1,
        'optin' => false,
        'newsletter' => '',
        'customer_since' => '2014-10-21',
        'last_name' => 'Oliver',
        'first_name' => 'April',
        'email' => 'april.oliver90@example.com',
        'phone1' => '',
        'phone2' => '',
        'gender' => 2,
        'birthday' => 0,
        'locale' => '_00',
        'date_last_order' => 0,
        'nb_order' => 0,
        'sum_order' => 0,
        'nb_order_year' => 0,
        'sum_order_year' => 0,
        'groups' => array(1),
        'active' => true,
        'addresses' => array(),
    );

    public function testItReturnsCorrectUserInformationsWhenCustomerHasNotPassedAnyOrder()
    {
        $GetUser = new SPM_ShopyMind_Action_GetUser(1);
        $result = $GetUser->process();
        $this->assertEquals($this->aCustomer, $result);
    }

    /**
     * @loadFixture userSubscribedToNewsletter
     */
    public function testItReturnsOptinWhenCustomerHasSubscribedToTheNewsletter()
    {
        $GetUser = new SPM_ShopyMind_Action_GetUser(1);
        $result = $GetUser->process();
        $this->assertTrue($result['optin']);
    }

    /**
     * @loadFixture userPassedSeveralOrders
     */
    public function testItReturnsCorrectOrderStatsWhenCustomerPassedSeveralOrders()
    {
        $GetUser = new SPM_ShopyMind_Action_GetUser(1);
        $result = $GetUser->process();
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
        $GetUser = new SPM_ShopyMind_Action_GetUser(array(
            'jane.doe34@example.com',
            'april.oliver90@example.com'
        ));
        $result = $GetUser->process();
        $this->assertCount(2, $result);
    }

    /**
     * @loadFixture anotherUserWithOrders
     */
    public function testItReturnsOnlyEmailAddressesFound()
    {
        $GetUser = new SPM_ShopyMind_Action_GetUser(array(
            'jane.doe34@example.com',
            'april.oliver90@example.com',
            'unknown-email@example.com',
        ));
        $result = $GetUser->process();
        $this->assertCount(2, $result);
    }

    /**
     * @loadFixture anotherUserWithOrders
     */
    public function testItDoesNotReturnOrderStatsWhenSearchingByCustomerEmails()
    {
        $GetUser = new SPM_ShopyMind_Action_GetUser(array(
            'jane.doe34@example.com',
            'april.oliver90@example.com'
        ));
        $results = $GetUser->process();

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
        $GetUser = new SPM_ShopyMind_Action_GetUser(array(
            'jane.doe34@example.com',
            'april.oliver90@example.com'
        ));
        $result = $GetUser->process();

        $expectedData = array(
            array_merge($this->aCustomer, array(
                'id_customer' => 'jane.doe34@example.com',
                'shop_id_shop' => 2,
                'customer_since' => '2015-01-21',
                'last_name' => 'Doe',
                'first_name' => 'Jane',
                'email' => 'jane.doe34@example.com',
                'phone1' => '0102030455',
                'locale' => 'FR',
                'gender' => 1,
                'groups' => array(1),
            )),
            array_merge($this->aCustomer, array(
                'id_customer' => 'april.oliver90@example.com',
                'customer_since' => '2014-02-06',
                'phone1' => '0102030405',
                'gender' => 2,
                'birthday' => '1962-08-29 00:00:00',
                'locale' => 'FR',
                'groups' => array(1),
            ))
        );
        $this->assertEquals($expectedData, $result);
    }

    /**
     * @loadFixture anotherUserWithQuotes
     */
    public function testItReturnsCorrectCustomerDataWhenSearchingInQuotes()
    {
        $GetUser = new SPM_ShopyMind_Action_GetUser(array(
            'jane.doe34@example.com',
            'april.oliver90@example.com'
        ), true);
        $result = $GetUser->process();

        $expectedData = array(
            array_merge($this->aCustomer, array(
                'id_customer' => 'jane.doe34@example.com',
                'shop_id_shop' => 2,
                'customer_since' => '2015-01-21',
                'last_name' => 'Doe',
                'first_name' => 'Jane',
                'email' => 'jane.doe34@example.com',
                'phone1' => '0102030455',
                'locale' => 'FR',
                'gender' => 1,
                'groups' => array(1),
            )),
            array_merge($this->aCustomer, array(
                'id_customer' => 'april.oliver90@example.com',
                'customer_since' => '2014-02-06',
                'phone1' => '0102030405',
                'gender' => 2,
                'birthday' => '1962-08-29 00:00:00',
                'locale' => 'FR',
                'groups' => array(1),
            ))
        );
        $this->assertEquals($expectedData, $result);
    }

    /**
     * @loadFixture addresses
     * @group mine
     */
    public function testItReturnCustomerAddresses()
    {
        $addresses = array(
            array(
                'id_address' => 1,
                'phone1' => '0102030405',
                'phone2' => '',
                'company' => 'Shopymind',
                'address1' => '92 rue Saint Jacques',
                'address2' => '',
                'postcode' => '13006',
                'city' => 'Marseille',
                'other' => '',
                'active' => '',
                'region' => '',
            ),
            array(
                'id_address' => 2,
                'phone1' => '0504030201',
                'phone2' => '',
                'company' => 'Occitech',
                'address1' => '12 route d\'Espagne',
                'address2' => '1er etage',
                'postcode' => '31100',
                'city' => 'Toulouse',
                'other' => '',
                'active' => '',
                'region' => '',
            ),
            array(
                'id_address' => 3,
                'phone1' => '',
                'phone2' => '',
                'company' => '',
                'address1' => '',
                'address2' => '',
                'postcode' => '',
                'city' => '',
                'other' => '',
                'active' => '',
                'region' => '',
            ),
        );
        $GetUser = new SPM_ShopyMind_Action_GetUser(1);
        $result = $GetUser->process();
        $this->assertEquals($addresses, $result['addresses']);
    }
}
