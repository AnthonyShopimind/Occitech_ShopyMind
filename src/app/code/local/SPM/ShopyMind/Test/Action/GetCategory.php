<?php

/**
 * Class SPM_ShopyMind_Test_Action_GetCategory
 * @group category
 */
class SPM_ShopyMind_Test_Action_GetCategory extends EcomDev_PHPUnit_Test_Case
{
    protected function tearDown()
    {
        parent::tearDown();

        if (session_id()) {
            session_destroy();
        }
    }

    /**
     * @loadFixture default
     */
    public function testProcessWithNotFoundCategoryReturnsEmptyArray()
    {
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId('');
        $Action = new SPM_ShopyMind_Action_GetCategory($scope, null);

        $this->assertEmpty($Action->process());
    }

    /**
     * @loadFixture default
     */
    public function testProcessWithExistingCategoryId()
    {
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId('store-1');
        $Action = new SPM_ShopyMind_Action_GetCategory($scope, 6);

        $expected = array(
            'entity_id' => 6,
            'parent_id' => 5,
            'name' => 'Hello world',
            'description' => 'this is REALLY a test category',
            'url_key' => 'hello-world',
            'created_at' => '2013-10-26 12:00:00',
            'is_active' => 1,
            'entity_type_id' => 3,
            'attribute_set_id' => 3,
            'updated_at' => '2013-10-26 13:00:00',
            'path' => '1/5/6',
            'position' => '1',
            'locale' => 'fr_FR',
            'level' => 1,
            'children_count' => 0,
            'include_in_menu' => 1
        );

        $category = $Action->process(false);
        $this->assertEquals($expected, $category->getData());
    }

    /**
     * @loadFixture default
     */
    public function testProcessWithDefinedScope()
    {
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId('website-2');
        $Action = new SPM_ShopyMind_Action_GetCategory($scope, 3);

        $expected = array(
            'entity_id' => 3,
            'parent_id' => 2,
            'name' => 'Test Category 1',
            'description' => 'this is a test category',
            'url_key' => 'test-category-1',
            'created_at' => '2013-10-26 12:00:00',
            'is_active' => 1,
            'entity_type_id' => 3,
            'attribute_set_id' => 3,
            'updated_at' => '2013-10-26 13:00:00',
            'path' => '1/2/3',
            'position' => '1',
            'level' => 2,
            'locale' => 'en_US',
            'children_count' => 0,
            'include_in_menu' => 1
        );

        $category = $Action->process(false);
        $this->assertEquals($expected, $category->getData());
    }

    /**
     * @loadFixture default
     */
    public function testProcessWithUndefinedScope()
    {
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId('');
        $Action = new SPM_ShopyMind_Action_GetCategory($scope, 6);

        $expected = array(
            'entity_id' => 6,
            'parent_id' => 5,
            'name' => 'Hello world',
            'description' => 'this is REALLY a test category',
            'url_key' => 'hello-world',
            'created_at' => '2013-10-26 12:00:00',
            'is_active' => 1,
            'entity_type_id' => 3,
            'attribute_set_id' => 3,
            'updated_at' => '2013-10-26 13:00:00',
            'path' => '1/5/6',
            'position' => '1',
            'locale' => 'en_US',
            'level' => 1,
            'children_count' => 0,
            'include_in_menu' => 1
        );

        $category = $Action->process(false);
        $this->assertEquals($expected, $category->getData());
    }
}
