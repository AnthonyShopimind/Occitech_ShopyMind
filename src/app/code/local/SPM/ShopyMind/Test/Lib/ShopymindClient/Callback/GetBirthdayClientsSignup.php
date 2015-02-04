<?php

/**
 * @loadSharedFixture
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_GetBirthdayClientsSignup extends EcomDev_PHPUnit_Test_Case
{
    public function testGetBirthdayClientsSignupShoulReturnEmptyIfNoCustomersMatchFilters() {
        $results = ShopymindClient_Callback::getBirthdayClientsSignUp(false, '2015-10-21 12:34:56', array());

        $this->assertEmpty($this->extractCustomerIds($results));
    }

    public function testGetBirthdayClientsSignupShoulReturnClientWhichCreateTheirAccountAtDateReference() {
        $results = ShopymindClient_Callback::getBirthdayClientsSignUp(false, '2014-10-21 12:34:56', array());

        $expected = array('1', '3', '4');
        $this->assertEquals($expected, $this->extractCustomerIds($results));
    }

    public function testGetBirthdayClientsSignupShoulReturnClientInCorrectCountry() {
        $results = ShopymindClient_Callback::getBirthdayClientsSignUp(false, '2014-10-21 12:34:56', array(array('country' => 'US')));

        $expected = array('3', '4');
        $this->assertEquals($expected, $this->extractCustomerIds($results));
    }

    public function testGetBirthdayClientsSignupShoulReturnClientInCorrectCountryAndRegion() {
        $results = ShopymindClient_Callback::getBirthdayClientsSignUp(false, '2014-10-21 12:34:56', array(array('country' => 'US', 'region' => 'AL')));

        $expected = array('3');
        $this->assertEquals($expected, $this->extractCustomerIds($results));
    }

    public function testGetBirthdayClientsSignupShoulReturnClientInCorrectRegion() {
        $results = ShopymindClient_Callback::getBirthdayClientsSignUp(false, '2014-10-21 12:34:56', array(array('region' => 'AL')));

        $expected = array('3');
        $this->assertEquals($expected, $this->extractCustomerIds($results));
    }

    public function testGetBirthdayClientsSignupShoulReturnClientInCorrectRegionsAndCountries() {
        $results = ShopymindClient_Callback::getBirthdayClientsSignUp(false, '2014-10-21 12:34:56', array(array('country' => 'US', 'region' => 'AL'), array('country' => 'FR')));

        $expected = array('3');
        $this->assertEquals($expected, $this->extractCustomerIds($results));
    }

    public function testGetBirthdayClientsSignupShoulReturnClientCountCorrespondingToFilters() {
        $results = ShopymindClient_Callback::getBirthdayClientsSignUp(false, '2014-10-21 12:34:56', array(), true);

        $expected = 3;
        $this->assertEquals($expected, $results['count']);
    }

    private function extractCustomerIds($customers) {
        return array_map(function($customer) { return $customer['customer']['id_customer']; }, $customers);
    }
}
