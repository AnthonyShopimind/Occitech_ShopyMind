<?php

/**
 * @loadSharedFixture
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_GetExistingEmails extends EcomDev_PHPUnit_Test_Case
{

    public function testItReturnsExistingEmailsFromDefaultStoreWithoutParameters()
    {
        $result = ShopymindClient_Callback::getExistingEmails();
        $expected = array(
            '1' => 'april.oliver90@example.com',
            '2' => 'august.oliver90@example.com',
            '3' => 'january.oliver90@example.com',
        );

        $this->assertEquals($expected, $result);
    }

    public function testItReturnsExistingEmailBeginningAtStartIndexIfLimitIsSet()
    {
        $result = ShopymindClient_Callback::getExistingEmails(null, 0, 1, null);
        $expected = array(
            '1' => 'april.oliver90@example.com',
        );

        $this->assertEquals($expected, $result);
    }

    public function testItReturnsExistingEmailWithoutStartIndexIfLimitIsNotSet()
    {
        $result = ShopymindClient_Callback::getExistingEmails(null, 0, null, null);
        $expected = array(
            '1' => 'april.oliver90@example.com',
            '2' => 'august.oliver90@example.com',
            '3' => 'january.oliver90@example.com',
        );

        $this->assertEquals($expected, $result);
    }

    public function testItReturnsExistingEmailWithUpdatedAtFieldEqualOrGreaterThanLastUpdateParameter()
    {
        $result = ShopymindClient_Callback::getExistingEmails(null, 0, null, '2015-01-31 18:00:00');
        $expected = array(
            '3' => 'january.oliver90@example.com',
        );

        $this->assertEquals($expected, $result);
    }

    public function testItReturnsExistingEmailOnlyFromStoreIdEqualToIdShopParameter()
    {
        $result = ShopymindClient_Callback::getExistingEmails(2, 0, null, null);
        $expected = array(
            '2' => 'august.oliver90@example.com',
            '3' => 'january.oliver90@example.com',
        );

        $this->assertEquals($expected, $result);
    }

    public function testItShouldReturnsExistingEmailMatchingAllTheConditions()
    {
        $result = ShopymindClient_Callback::getExistingEmails(2, 1, 1, '2015-01-15 15:00:00');
        $expected = array(
            '3' => 'january.oliver90@example.com',
        );

        $this->assertEquals($expected, $result);
    }

    public function testItReturnsEmptyArrayIfNothingMatch()
    {
        $result = ShopymindClient_Callback::getExistingEmails(1, 1, 1, '2015-01-15 15:00:00');
        $this->assertEmpty($result);
    }
}
