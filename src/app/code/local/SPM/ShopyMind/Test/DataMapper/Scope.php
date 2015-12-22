<?php

/**
 * Class SPM_ShopyMind_Test_DataMapper_Scope
 * @loadSharedFixture
 */
class SPM_ShopyMind_Test_DataMapper_Scope extends EcomDev_PHPUnit_Test_Case
{
    protected function setUp()
    {
        parent::setUp();
        $this->replaceByMock('model', 'core/session', $this->guestSession());
    }

    public function testFormatWithUnRestrictedScopeShouldReturnedMagentoDefaultInformation()
    {
        $ScopeFormatter = new SPM_ShopyMind_DataMapper_Scope();
        $scope = SPM_ShopyMind_Model_Scope::buildUnrestricted();

        $actual = $ScopeFormatter->format($scope);
        $expected = array(
            'shop_id_shop' => 0,
            'lang' => 'en',
            'currency' => 'USD'
        );

        $this->assertEquals($expected, $actual);
    }

    public function testFormatWithRestrictedScopeShouldReturnedShopInformation()
    {
        $ScopeFormatter = new SPM_ShopyMind_DataMapper_Scope();
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId('website-2');

        $actual = $ScopeFormatter->format($scope);
        $expected = array(
            'shop_id_shop' => 2,
            'lang' => 'fr',
            'currency' => 'EUR'
        );

        $this->assertEquals($expected, $actual);
    }
}
