<?php

/**
 * @group category
 */
class SPM_ShopyMind_Test_DataMapper_Category extends EcomDev_PHPUnit_Test_Case
{
    private $SUT;

    protected function setUp()
    {
        parent::setUp();
        $this->SUT = new SPM_ShopyMind_DataMapper_Category();
    }

    /**
     * @loadFixture default
     */
    public function testFieldsAreCorrectlyMapped()
    {
        $expected = array(
            'shop_id_shop' => 1,
            'id_category' => 6,
            'id_parent_category' => 5,
            'lang' => 'fr_FR',
            'name' => 'Hello world',
            'description' => 'this is REALLY a test category',
            'link' => 'http://shopymind.test/hello-world',
            'date_creation' => '2013-10-26 12:00:00',
            'active' => '1',
        );

        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId('store-1');
        $Action = new SPM_ShopyMind_Action_GetCategory($scope, 6);
        $category = $Action->process();

        $this->assertEquals($expected, $this->SUT->format($category));
    }
}
