<?php

/**
 * @group category
 * @group dataMappers
 */
class SPM_ShopyMind_Test_DataMapper_Category extends EcomDev_PHPUnit_Test_Case
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
    public function testFieldsAreCorrectlyMapped()
    {
        $expected = array(
            'shop_id_shop' => 1,
            'id_category' => 3,
            'id_parent_category' => 2,
            'lang' => 'en',
            'name' => 'Test Category 1',
            'description' => 'this is a test category',
            'link' => 'http://shopymind.test/catalog/category/view/s/test-category-1/id/3/',
            'date_creation' => '2013-10-26 12:00:00',
            'active' => '1',
        );

        $category = Mage::getModel('catalog/category')->load(3);

        $scope = SPM_ShopyMind_Model_Scope::buildUnrestricted();
        $Formatter = new SPM_ShopyMind_DataMapper_Category($scope);
        $actual = $Formatter->format($category);

        $this->assertEquals($expected, $actual);
    }
}
