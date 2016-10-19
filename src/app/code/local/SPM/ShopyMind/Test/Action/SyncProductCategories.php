<?php

/**
 * @loadSharedFixture
 * @group actions
 */
class SPM_ShopyMind_Test_Action_SyncProductCategories extends PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        parent::tearDown();

        if (session_id()) {
            session_destroy();
        }
    }

    public function testRetrieveCategoriesWithoutRestrictions()
    {
        $scope = SPM_ShopyMind_Model_Scope::buildUnrestricted();
        $SyncCategories = new SPM_ShopyMind_Action_SyncProductCategories($scope, null, null, null, false, false);

        $actual = $SyncCategories->retrieveCategories()->getAllIds();
        $expected = array(1, 2, 3, 4, 5, 6);

        $this->assertEquals($expected, array_values($actual));
    }

    public function testRetrieveCategoriesWithScopeRestrictions()
    {
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId('website-1');
        $SyncCategories = new SPM_ShopyMind_Action_SyncProductCategories($scope, null, null, null, false, false);

        $actual = $SyncCategories->retrieveCategories()->getAllIds();
        $expected = array(5, 6);

        $this->assertEquals($expected, array_values($actual));
    }

    public function testRetrieveCategoriesWithScopeRestrictionsAndLimit()
    {
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId('website-1');
        $SyncCategories = new SPM_ShopyMind_Action_SyncProductCategories($scope, 1, 2, null, false, false);

        $actual = $SyncCategories->retrieveCategories();
        $expected = 6;

        $this->assertCount(1, $actual);
        $this->assertEquals($expected, $actual->getFirstItem()->getId());
    }

    public function testRetrieveCategoriesWithLastUpdate()
    {
        $scope = SPM_ShopyMind_Model_Scope::buildUnrestricted();
        $SyncCategories = new SPM_ShopyMind_Action_SyncProductCategories($scope, null, null, '2015-12-14 13:00:00', false, false);

        $actual = $SyncCategories->retrieveCategories()->getAllIds();
        $expected = array(1);

        $this->assertEquals($expected, array_values($actual));
    }

    public function testRetrieveCategoriesWithSpecificCategoryId()
    {
        $scope = SPM_ShopyMind_Model_Scope::buildUnrestricted();
        $SyncCategories = new SPM_ShopyMind_Action_SyncProductCategories($scope, null, null, null, 2, false);

        $actual = $SyncCategories->retrieveCategories()->getAllIds();
        $expected = array(2);

        $this->assertEquals($expected, array_values($actual));
    }

    public function testRetrieveCategoriesWithJustCount()
    {
        $scope = SPM_ShopyMind_Model_Scope::buildUnrestricted();
        $SyncCategories = new SPM_ShopyMind_Action_SyncProductCategories($scope, null, null, null, false, true);

        $actual = $SyncCategories->process();
        $expected = 6;

        $this->assertEquals($expected, $actual);
    }

    public function testRetrieveCategoriesShouldReturnFormattedData()
    {
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId('store-1');
        $SyncCategories = new SPM_ShopyMind_Action_SyncProductCategories($scope, null, null, null, 6, false);

        $actual = $SyncCategories->process();

        $expected = array(
            '6-fr' => array(
                'shop_id_shop' => 1,
                'id_category' => 6,
                'id_parent_category' => 5,
                'lang' => 'fr',
                'name' => 'Hello world',
                'description' => 'this is REALLY a test category',
                'link' => 'http://shopymind.test/catalog/category/view/s/hello-world/id/6/',
                'date_creation' => '2015-12-10 12:00:00',
                'active' => '1',
            )
        );

        $this->assertEquals($expected, $actual);
    }
}
