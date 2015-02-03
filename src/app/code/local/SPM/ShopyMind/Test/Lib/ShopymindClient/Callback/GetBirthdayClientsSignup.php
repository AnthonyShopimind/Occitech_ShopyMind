<?php

/**
 * @loadSharedFixture
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_GetBirthdayClientsSignup extends EcomDev_PHPUnit_Test_Case
{
    public function testGetBirthdayClientsSignupShoulReturnEmptyIfNoCustomersMatchFilters() {
        $results = ShopymindClient_Callback::getBirthdayClientsSignUp(1, '2015-10-21 12:34:56', array());
        $resultCustomerIds = array_map(function($customer) { return $customer['customer']['id_customer']; }, $results);

        $this->assertEmpty($resultCustomerIds);
    }

    public function testGetBirthdayClientsSignupShoulReturnClientWhichCreateTheirAccountAtDateReference() {
        $results = ShopymindClient_Callback::getBirthdayClientsSignUp(1, '2014-10-21 12:34:56', array());
        $resultCustomerIds = array_map(function($customer) { return $customer['customer']['id_customer']; }, $results);

        $expected = array('1', '3', '4');
        $this->assertEquals($expected, $resultCustomerIds);
    }

    public function testGetBirthdayClientsSignupShoulReturnClientInCorrectCountry() {
        $results = ShopymindClient_Callback::getBirthdayClientsSignUp(1, '2014-10-21 12:34:56', array(array('country' => 'US')));
        $resultCustomerIds = array_map(function($customer) { return $customer['customer']['id_customer']; }, $results);

        $expected = array('3', '4');
        $this->assertEquals($expected, $resultCustomerIds);
    }

    public function testGetBirthdayClientsSignupShoulReturnClientInCorrectCountryAndRegion() {
        $results = ShopymindClient_Callback::getBirthdayClientsSignUp(1, '2014-10-21 12:34:56', array(array('country' => 'US', 'region' => 'AL')));
        $resultCustomerIds = array_map(function($customer) { return $customer['customer']['id_customer']; }, $results);

        $expected = array('3');
        $this->assertEquals($expected, $resultCustomerIds);
    }

    public function testGetBirthdayClientsSignupShoulReturnClientInCorrectRegion() {
        $results = ShopymindClient_Callback::getBirthdayClientsSignUp(1, '2014-10-21 12:34:56', array(array('region' => 'AL')));
        $resultCustomerIds = array_map(function($customer) { return $customer['customer']['id_customer']; }, $results);

        $expected = array('3');
        $this->assertEquals($expected, $resultCustomerIds);
    }

    public function testGetBirthdayClientsSignupShoulReturnClientInCorrectRegionsAndCountries() {
        $results = ShopymindClient_Callback::getBirthdayClientsSignUp(1, '2014-10-21 12:34:56', array(array('country' => 'US', 'region' => 'AL'), array('country' => 'FR')));
        $resultCustomerIds = array_map(function($customer) { return $customer['customer']['id_customer']; }, $results);

        $expected = array('3');
        $this->assertEquals($expected, $resultCustomerIds);
    }

    public function testGetBirthdayClientsSignupShoulReturnClientCountCorrespondingToFilters() {
        $results = ShopymindClient_Callback::getBirthdayClientsSignUp(1, '2014-10-21 12:34:56', array(), true);

        $expected = 3;
        $this->assertEquals($expected, $results['count']);
    }

}
