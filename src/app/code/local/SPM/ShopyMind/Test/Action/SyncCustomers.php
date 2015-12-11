<?php

/**
 * @loadSharedFixture
 * @group actions
 */
class SPM_ShopyMind_Test_Action_SyncCustomers extends PHPUnit_Framework_TestCase
{
    public function testRetrieveCustomerIdsWithNoRestrictions()
    {
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId('');
        $SyncCustomers = new SPM_ShopyMind_Action_SyncCustomers($scope, null, null, null);

        $actual = $SyncCustomers->retrieveCustomerIds();
        $expected = array(1, 2, 3);

        $this->assertEquals($expected, $actual);
    }

    public function testRetrieveCustomerIdsWithShopRestriction()
    {
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId('website-1');
        $SyncCustomers = new SPM_ShopyMind_Action_SyncCustomers($scope, null, null, null);

        $actual = $SyncCustomers->retrieveCustomerIds();
        $expected = array(1);

        $this->assertEquals($expected, $actual);
    }

    public function testRetrieveCustomerIdsWithLimit()
    {
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId('');
        $SyncCustomers = new SPM_ShopyMind_Action_SyncCustomers($scope, 1, 2, null);

        $actual = $SyncCustomers->retrieveCustomerIds();
        $expected = array(2, 3);

        $this->assertEquals($expected, $actual);
    }

    public function testRetrieveCustomerIdsWithLastUpdate()
    {
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId('');
        $SyncCustomers = new SPM_ShopyMind_Action_SyncCustomers($scope, null, null, '2015-01-20 00:00:00');

        $actual = $SyncCustomers->retrieveCustomerIds();
        $expected = array(3);

        $this->assertEquals($expected, $actual);
    }

    public function testRetrieveCustomerIdsWithSpecificCustomerId()
    {
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId('');
        $SyncCustomers = new SPM_ShopyMind_Action_SyncCustomers($scope, null, null, null, 2);

        $actual = $SyncCustomers->retrieveCustomerIds();
        $expected = array(2);

        $this->assertEquals($expected, $actual);
    }

    public function testProcesssWithJustCountOption()
    {
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId('');
        $SyncCustomers = new SPM_ShopyMind_Action_SyncCustomers($scope, null, null, null, false, true);

        $actual = $SyncCustomers->process();
        $expected = 3;

        $this->assertEquals($expected, $actual);
    }

    public function testProcessReturnCustomerFormattedByGetUserMethod()
    {

        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId('');
        $SyncCustomers = new SPM_ShopyMind_Action_SyncCustomers($scope, null, null, null, 2);

        $actual = $SyncCustomers->process();
        $expected = array(ShopymindClient_Callback::getUser(2));

        $this->assertEquals($expected, $actual);
    }
}
