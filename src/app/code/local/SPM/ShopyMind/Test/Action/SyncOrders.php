<?php

/**
 * @loadSharedFixture
 * @group actions
 */
class SPM_ShopyMind_Test_Action_SyncOrders extends PHPUnit_Framework_TestCase
{
    public function testRetrieveOrdersWithoutRestrictions()
    {
        $actual = $this->actualOrdersIdsFromSyncOrders('', null, null, null, false, false);
        $expected = array(1, 2, 3);

        $this->assertEquals($expected, $actual);
    }

    public function testRetrieveOrdersWithShopRestriction()
    {
        $actual = $this->actualOrdersIdsFromSyncOrders('website-2', null, null, null, false, false);
        $expected = array(1);

        $this->assertEquals($expected, $actual);
    }

    public function testRetrieveOrdersWithLimit()
    {
        $actual = $this->actualOrdersIdsFromSyncOrders('', 1, 2, null, false, false);
        $expected = array(3, 1);

        $this->assertEquals($expected, $actual);
    }

    public function testRetrieveOrdersWithLastUpdate()
    {
        $actual = $this->actualOrdersIdsFromSyncOrders('', null, null, '2015-12-09 00:00:00', false, false);
        $expected = array(1);

        $this->assertEquals($expected, $actual);
    }

    public function testRetrieveOrdersWithSpecificOrderId()
    {
        $actual = $this->actualOrdersIdsFromSyncOrders('', null, null, null, 2, false);
        $expected = array(2);

        $this->assertEquals($expected, $actual);
    }

    public function testRetrieveOrdersWithSpecificOrderIds()
    {
        $actual = $this->actualOrdersIdsFromSyncOrders('', null, null, null, array(2, 3), false);
        $expected = array(2, 3);

        $this->assertEquals($expected, $actual);
    }

    public function testProcessWithJustCountOption()
    {
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId('');
        $SyncOrders = new SPM_ShopyMind_Action_SyncOrders($scope, null, null, null, false, true);

        $actual = $SyncOrders->process();
        $expected = 3;

        $this->assertEquals($expected, $actual);
    }

    private function actualOrdersIdsFromSyncOrders($shopId, $start, $limit, $lastUpdate, $orderId = false, $justCount = false)
    {
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId($shopId);
        $SyncOrders = new SPM_ShopyMind_Action_SyncOrders($scope, $start, $limit, $lastUpdate, $orderId, $justCount);
        $result = $SyncOrders->retrieveOrders();

        $actual = array();
        foreach($result as $order) {
            $actual[] = $order->getId();
        }

        return $actual;
    }
}
