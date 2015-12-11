<?php

/**
 * @loadSharedFixture
 * @group actions
 */
class SPM_ShopyMind_Test_Action_SyncCustomers extends PHPUnit_Framework_TestCase
{
    public function testRetrieveCustomerIdsWithNoRestrictions()
    {
        $params = array(
            'idShop' => '',
            'start' => null,
            'limit' => null,
            'lastUpdate' => null,
        );
        $SyncCustomers = new SPM_ShopyMind_Action_SyncCustomers($params);
        $actual = $SyncCustomers->retrieveCustomerIds();
        $expected = array(1, 2, 3);

        $this->assertEquals($expected, $actual);
    }

    public function testRetrieveCustomerIdsWithShopRestriction()
    {
        $params = array(
            'idShop' => 'website-1',
            'start' => null,
            'limit' => null,
            'lastUpdate' => null,
        );
        $SyncCustomers = new SPM_ShopyMind_Action_SyncCustomers($params);
        $actual = $SyncCustomers->retrieveCustomerIds();
        $expected = array(1);

        $this->assertEquals($expected, $actual);
    }

    public function testRetrieveCustomerIdsWithLimit()
    {
        $params = array(
            'idShop' => '',
            'start' => 1,
            'limit' => 2,
            'lastUpdate' => null,
        );
        $SyncCustomers = new SPM_ShopyMind_Action_SyncCustomers($params);
        $actual = $SyncCustomers->retrieveCustomerIds();
        $expected = array(2, 3);

        $this->assertEquals($expected, $actual);
    }

    public function testRetrieveCustomerIdsWithLastUpdate()
    {
        $params = array(
            'idShop' => '',
            'start' => null,
            'limit' => null,
            'lastUpdate' => '2015-01-20 00:00:00',
        );
        $SyncCustomers = new SPM_ShopyMind_Action_SyncCustomers($params);
        $actual = $SyncCustomers->retrieveCustomerIds();
        $expected = array(3);

        $this->assertEquals($expected, $actual);
    }

    public function testRetrieveCustomerIdsWithSpecificCustomerId()
    {
        $params = array(
            'idShop' => '',
            'start' => null,
            'limit' => null,
            'lastUpdate' => null,
            'customerId' => 2,
        );
        $SyncCustomers = new SPM_ShopyMind_Action_SyncCustomers($params);
        $actual = $SyncCustomers->retrieveCustomerIds();
        $expected = array(2);

        $this->assertEquals($expected, $actual);
    }

    public function testProcesssWithJustCountOption()
    {
        $params = array(
            'idShop' => '',
            'start' => null,
            'limit' => null,
            'lastUpdate' => null,
            'customerId' => false,
            'justCount' => true,
        );
        $SyncCustomers = new SPM_ShopyMind_Action_SyncCustomers($params);
        $actual = $SyncCustomers->process();
        $expected = 3;

        $this->assertEquals($expected, $actual);
    }

    public function testProcessReturnCustomerFormattedByGetUserMethod()
    {
        $params = array(
            'idShop' => '',
            'start' => null,
            'limit' => null,
            'lastUpdate' => null,
            'customerId' => 2,
        );
        $SyncCustomers = new SPM_ShopyMind_Action_SyncCustomers($params);
        $actual = $SyncCustomers->process();
        $expected = array(ShopymindClient_Callback::getUser(2));

        $this->assertEquals($expected, $actual);
    }
}
