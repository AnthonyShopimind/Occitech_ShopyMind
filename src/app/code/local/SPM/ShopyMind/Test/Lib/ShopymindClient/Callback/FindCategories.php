<?php
if (Mage::getVersion() < 1.6) :
    class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_FindCategories extends PHPUnit_Framework_TestCase
    {
        public function testNothing()
        {
            $this->markTestSkipped(
                'Impossible to run the testsuite on Magento 1.5 since the fixture loader is broken:
                see https://github.com/EcomDev/EcomDev_PHPUnit/pull/229'
            );
        }
    }
else :
/**
 * @loadSharedFixture
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_FindCategories extends EcomDev_PHPUnit_Test_Case
{

    public function testItReturnsAnEmptyArrayWhenNoCategoriesMatch()
    {
        $result = ShopymindClient_Callback::findCategories(false, false, 'unmatched term');
        $this->assertEquals(array(), $result);
    }

    public function testCategoriesMatchWhenTheNameContainsTheSearchedTerm()
    {
        $result = ShopymindClient_Callback::findCategories(false, false, 'site cat');
        $expected = array(
            array('id' => 2, 'name' => 'Website Category')
        );
        $this->assertEquals($expected, $result);
    }

    public function testOnlyActiveCategoriesAreReturned()
    {
        $categoriesIds = array_map(
            function($category) { return $category['id']; },
            ShopymindClient_Callback::findCategories(false, false, 'category')
        );
        sort($categoriesIds);

        $expectedIds = array(2, 3, 5);
        $this->assertEquals($expectedIds, $categoriesIds);
    }

    public function testCategoriesAreOrderedAlphabetically()
    {
        $categoriesNames = array_map(
            function($category) { return $category['name']; },
            ShopymindClient_Callback::findCategories(false, false, 'category')
        );

        $expected = array('Another root category', 'Test Category 1', 'Website Category');
        $this->assertEquals($expected, $categoriesNames);
    }

    public function testCategoriesCanBeFilteredByStore()
    {
        $result = ShopymindClient_Callback::findCategories('store-1', false, 'category');
        $expected = array(
            array('id' => 5, 'name' => 'Another root category')
        );
        $this->assertEquals($expected, $result);
    }

    public function testCategoriesCanBeFilteredByWebsite()
    {
        $result = ShopymindClient_Callback::findCategories('website-1', false, 'category');
        $expected = array(
            array('id' => 5, 'name' => 'Another root category')
        );
        $this->assertEquals($expected, $result);
    }

    public function testNoCategoriesMatchWhenSearchTermIsLessThan3Chars()
    {
        $result = ShopymindClient_Callback::findCategories(false, false, 'ca');
        $this->assertEquals(array(), $result);
    }

}
endif;
