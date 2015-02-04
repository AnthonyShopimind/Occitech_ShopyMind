<?php

/**
 * @loadSharedFixture
 */
class SPM_ShopyMind_Test_Model_Scope extends EcomDev_PHPUnit_Test_Case
{

    public function testNoFilterWillReturnAllActiveStoreIds()
    {
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId(false);

        $result = $scope->storeIds();
        sort($result);

        $allStoreIds = array(1, 2, 3);
        $this->assertEquals($allStoreIds, $result);
    }

    public function testStoreScopeOnlyReturnsOneStore()
    {
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId('store-2');
        $result = $scope->storeIds();
        $this->assertEquals(array(2), $result);
    }

    public function testWebsiteScopeOnlyReturnsStoresFromWebsite()
    {
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId('website-2');

        $result = $scope->storeIds();
        sort($result);

        $this->assertEquals(array(2, 3), $result);
    }

    public function testLangRestrictionOnlyReturnsOneStore()
    {
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId(false, 'FR');
        $result = $scope->storeIds();
        $this->assertEquals(array(2), $result);
    }

}
