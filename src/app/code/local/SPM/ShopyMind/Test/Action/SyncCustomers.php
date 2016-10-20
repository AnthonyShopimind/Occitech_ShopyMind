<?php

/**
 * @loadSharedFixture
 * @group actions
 * @group customers
 */
class SPM_ShopyMind_Test_Action_SyncCustomers extends EcomDev_PHPUnit_Test_Case
{
    public function testRetrieveCustomerEmailsWithoutRestrictions()
    {
        $scope = SPM_ShopyMind_Model_Scope::buildUnrestricted();
        $SyncCustomers = new SPM_ShopyMind_Action_SyncCustomers($scope, null, null, null, false, false);

        $actual = $SyncCustomers->retrieveCustomerIds();
        $expected = array(1, 2, 3);

        $this->assertEquals($expected, array_values($actual));
    }

    public function testRetrieveCustomerEmailsWithShopRestriction()
    {
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId('website-1');
        $SyncCustomers = new SPM_ShopyMind_Action_SyncCustomers($scope, null, null, null, false, false);

        $actual = $SyncCustomers->retrieveCustomerIds();
        $expected = array(1);

        $this->assertEquals($expected, array_values($actual));
    }

    public function testRetrieveCustomerEmailsWithLimit()
    {
        $scope = SPM_ShopyMind_Model_Scope::buildUnrestricted();
        $SyncCustomers = new SPM_ShopyMind_Action_SyncCustomers($scope, 1, 2, null, false, false);

        $actual = $SyncCustomers->retrieveCustomerIds();
        $expected = array(2, 3);

        $this->assertEquals($expected, array_values($actual));
    }

    public function testRetrieveCustomerEmailsWithLastUpdate()
    {
        $scope = SPM_ShopyMind_Model_Scope::buildUnrestricted();
        $SyncCustomers = new SPM_ShopyMind_Action_SyncCustomers($scope, null, null, '2015-01-20 00:00:00', false, false);

        $actual = $SyncCustomers->retrieveCustomerIds();
        $expected = array(3);

        $this->assertEquals($expected, array_values($actual));
    }

    public function testRetrieveCustomerEmailsWithSpecificCustomerId()
    {
        $scope = SPM_ShopyMind_Model_Scope::buildUnrestricted();
        $SyncCustomers = new SPM_ShopyMind_Action_SyncCustomers($scope, null, null, null, 2, false);

        $actual = $SyncCustomers->retrieveCustomerIds();
        $expected = 2;

        $this->assertEquals($expected, $actual);
    }

    public function testProcesssWithJustCountOption()
    {
        $scope = SPM_ShopyMind_Model_Scope::buildUnrestricted();
        $SyncCustomers = new SPM_ShopyMind_Action_SyncCustomers($scope, null, null, null, false, true);

        $actual = $SyncCustomers->process();
        $expected = 3;

        $this->assertEquals($expected, $actual);
    }

    public function testProcessReturnCustomerFormattedByGetUserMethodAlwaysAsArray()
    {
        $scope = SPM_ShopyMind_Model_Scope::buildUnrestricted();
        $SyncCustomers = new SPM_ShopyMind_Action_SyncCustomers($scope, null, null, null, 2, false);
        $actual = $SyncCustomers->process();

        $GetUser = new SPM_ShopyMind_Action_GetUser(2);
        $expected = [$GetUser->process()];

        $this->assertEquals($expected, $actual);
    }
}
