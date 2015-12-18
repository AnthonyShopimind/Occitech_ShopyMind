<?php

/**
 * Class SPM_ShopyMind_Test_Action_SyncProducts
 * @group actions
 * @group 57
 * @loadSharedFixture
 */
class SPM_ShopyMind_Test_Action_SyncProducts extends EcomDev_PHPUnit_Test_Case
{
    public function tearDown()
    {
        parent::tearDown();
    }

    public function testRetrieveProductsWithoutRestrictions()
    {
        $scope = SPM_ShopyMind_Model_Scope::buildUnrestricted();
        $SyncProducts = new SPM_ShopyMind_Action_SyncProducts($scope, null, null, null, false, false);

        $actual = $SyncProducts->retrieveProducts()->getAllIds();
        $expected = array(1, 2, 3, 4, 5);

        $this->assertEquals($expected, array_values($actual));
    }

    public function testRetrieveProductsWithOffset()
    {
        $scope = SPM_ShopyMind_Model_Scope::buildUnrestricted();
        $SyncProducts = new SPM_ShopyMind_Action_SyncProducts($scope, 2, null, null, false, false);

        $actual = $SyncProducts->retrieveProducts();
        $expected = array(3, 4, 5);

        $this->assertCount(3, $actual->getItems());
        $this->assertEquals($expected, array_values(array_map(function($product) { return $product->getId(); }, $actual->getItems())));
    }

    public function testRetrieveProductsWithLimit()
    {
        $scope = SPM_ShopyMind_Model_Scope::buildUnrestricted();
        $SyncProducts = new SPM_ShopyMind_Action_SyncProducts($scope, null, 2, null, false, false);

        $actual = $SyncProducts->retrieveProducts();
        $expected = array(1, 2);

        $this->assertCount(2, $actual->getItems());
        $this->assertEquals($expected, array_values(array_map(function($product) { return $product->getId(); }, $actual->getItems())));
    }

    public function testRetrieveProductsWithLimitAndOffset()
    {
        $scope = SPM_ShopyMind_Model_Scope::buildUnrestricted();
        $SyncProducts = new SPM_ShopyMind_Action_SyncProducts($scope, 3, 2, null, false, false);

        $actual = $SyncProducts->retrieveProducts();
        $expected = array(4, 5);

        $this->assertCount(2, $actual->getItems());
        $this->assertEquals($expected, array_values(array_map(function($product) { return $product->getId(); }, $actual->getItems())));
    }

    public function testRetrieveProductsWithProductId()
    {
        $scope = SPM_ShopyMind_Model_Scope::buildUnrestricted();
        $SyncProducts = new SPM_ShopyMind_Action_SyncProducts($scope, null, null, null, 1, false);

        $actual = $SyncProducts->retrieveProducts();
        $expected = 1;

        $this->assertCount(1, $actual->getItems());
        $this->assertEquals(array($expected), array_values(array_map(function($product) { return $product->getId(); }, $actual->getItems())));
    }

    public function testRetrieveProductsWithProductIds()
    {
        $scope = SPM_ShopyMind_Model_Scope::buildUnrestricted();
        $SyncProducts = new SPM_ShopyMind_Action_SyncProducts($scope, null, null, null, array(1, 2), false);

        $actual = $SyncProducts->retrieveProducts();
        $expected = array(1, 2);

        $this->assertCount(2, $actual->getItems());
        $this->assertEquals($expected, array_values(array_map(function($product) { return $product->getId(); }, $actual->getItems())));
    }

    public function testRetrieveProductsWithLastUpdate()
    {
        $scope = SPM_ShopyMind_Model_Scope::buildUnrestricted();
        $SyncProducts = new SPM_ShopyMind_Action_SyncProducts($scope, null, null, "2015-12-16 10:30:00", false, false);

        $actual = $SyncProducts->retrieveProducts();
        $expected = array(4, 5);

        $this->assertCount(2, $actual->getItems());
        $this->assertEquals($expected, array_values(array_map(function($product) { return $product->getId(); }, $actual->getItems())));
    }

    public function testRetrieveProductsWithJustCountOption()
    {
        $scope = SPM_ShopyMind_Model_Scope::buildUnrestricted();
        $SyncProducts = new SPM_ShopyMind_Action_SyncProducts($scope, null, null, "2015-12-16 10:30:00", false, true);

        $actual = $SyncProducts->process();
        $expected = 2;

        $this->assertEquals($expected, $actual);
    }

    public function testRetrieveProductsWithWebsiteScope()
    {
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId('website-2');
        $SyncProducts = new SPM_ShopyMind_Action_SyncProducts($scope, null, null, null, 1, false);

        $actual = $SyncProducts->retrieveProducts();

        $expected = 'Premier super produit';

        $this->assertEquals(array($expected), array_values(array_map(function($product) { return $product->getName(); }, $actual->getItems())));
    }

    public function testProcessActionWithMockedFormatter()
    {
        $MockedDataMapper = $this->getMock('SPM_ShopyMind_DataMapper_Product', array('format'));
        $MockedDataMapper->expects($this->any())
            ->method('format')
            ->with($this->anything())
            ->will($this->returnCallback(function() {
                $product = func_get_arg(0);
                return $product->getData();
            }));

        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId('website-2');
        $SyncProducts = new SPM_ShopyMind_Action_SyncProducts($scope, null, null, null, 1, false);
        $SyncProducts->setProductDataMapper($MockedDataMapper);

        $actual = $SyncProducts->process();
        $expectedKeys = array('shop_id_shop', 'lang', 'currency');

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $actual[1], 'The key : "' . $expectedKey . '" was not found');
        }
    }
}
