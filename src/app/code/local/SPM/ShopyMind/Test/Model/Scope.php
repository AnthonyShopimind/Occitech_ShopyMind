<?php

/**
 * @loadSharedFixture
 * @doNotIndexAll
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

    public function testShopymindIdRemainsIdentical()
    {
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId('website-2');
        $this->assertEquals('website-2', $scope->shopyMindId());
    }

    public function testShopymindIdForGlobalScopeHasCorrectValue()
    {
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId(false);
        $this->assertEquals('default-0', $scope->shopyMindId());
    }

    public function testFromCodesIsGlobalScopeWhenNoCode()
    {
        $scope = SPM_ShopyMind_Model_Scope::fromMagentoCodes(null, null);
        $this->assertEquals('default-0', $scope->shopyMindId());
    }

    public function testFromCodesIsWebsiteScopeWhenOnlyWebsiteCode()
    {
        $scope = SPM_ShopyMind_Model_Scope::fromMagentoCodes('b2c', null);
        $this->assertEquals('website-1', $scope->shopyMindId());
    }

    public function testFromCodesIsStoreScopeWhenBothWebsiteAndStoreCodes()
    {
        $scope = SPM_ShopyMind_Model_Scope::fromMagentoCodes('b2b', 'b2b_en');
        $this->assertEquals('store-3', $scope->shopyMindId());
    }

    /**
     * @loadFixture configurations
     */
    public function testConfigWillRetrieveWebsiteConfig()
    {
        $scope = SPM_ShopyMind_Model_Scope::fromMagentoCodes('b2c', null);
        $value = $scope->getConfig('foo/bar');
        $this->assertEquals('baz', $value);
    }

    /**
     * @loadFixture configurations
     */
    public function testConfigWillRetrieveDefaultConfigWhenWebsiteHasNot()
    {
        $scope = SPM_ShopyMind_Model_Scope::fromMagentoCodes('b2b', null);
        $value = $scope->getConfig('foo/bar');
        $this->assertEquals('hello', $value);
    }

    /**
     * @loadFixture order
     */
    public function testFromOrderWillRetrieveOrderLang()
    {
        $order = Mage::getModel('sales/order')->load(1);
        $scope = SPM_ShopyMind_Model_Scope::fromOrder($order);
        $this->assertEquals($scope->getLang(), 'en_GB');
    }

    /**
     * @loadFixture order
     */
    public function testFromOrderWillRetrieveOrderStore()
    {
        $order = Mage::getModel('sales/order')->load(1);
        $scope = SPM_ShopyMind_Model_Scope::fromOrder($order);
        $this->assertEquals($scope->getId(), 1);
    }
}
