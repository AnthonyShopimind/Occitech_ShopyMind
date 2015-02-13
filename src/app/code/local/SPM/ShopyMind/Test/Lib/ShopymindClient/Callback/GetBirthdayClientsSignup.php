<?php

/**
 * @loadSharedFixture
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_GetBirthdayClientsSignup extends EcomDev_PHPUnit_Test_Case
{
    public function testGetBirthdayClientsSignupShoulReturnEmptyIfNoCustomersMatchFilters()
    {
        $results = ShopymindClient_Callback::getBirthdayClientsSignUp(false, '2015-11-21 12:34:56', array(array('country' => 'FR'), array('country' => 'US')));
        $this->assertEmpty($results);
    }

    public function testGetBirthdayClientsSignupShouldNotReturnClientWhoCreatedTheirAccountAtDateReference()
    {
        $results = ShopymindClient_Callback::getBirthdayClientsSignUp(false, '2014-10-21 12:34:56', array(array('country' => 'FR'), array('country' => 'US')));
        $this->assertEmpty($results);
    }

    public function testGetBirthdayClientsSignupShouldReturnCustomersWhoCreatedTheirAccountOneYearAgo()
    {
        $results = ShopymindClient_Callback::getBirthdayClientsSignUp(false, '2015-10-21 12:34:56', array(array('country' => 'FR'), array('country' => 'US')));
        $expected = array('1', '3', '4');
        $this->assertCustomerIdsEquals($expected, $results);
    }

    public function testGetBirthdayClientsSignupShoulReturnClientInCorrectCountry()
    {
        $results = ShopymindClient_Callback::getBirthdayClientsSignUp(false, '2015-10-21 12:34:56', array(
            array('country' => 'US')
        ));

        $expected = array('3', '4');
        $this->assertCustomerIdsEquals($expected, $results);
    }

    public function testGetBirthdayClientsSignupShoulReturnClientInCorrectCountryAndRegion()
    {
        $results = ShopymindClient_Callback::getBirthdayClientsSignUp(false, '2015-10-21 12:34:56', array(
            array('country' => 'US', 'region' => 'AL')
        ));

        $expected = array('3');
        $this->assertCustomerIdsEquals($expected, $results);
    }

    public function testGetBirthdayClientsSignupShoulReturnClientInCorrectRegionsAndCountries()
    {
        $results = ShopymindClient_Callback::getBirthdayClientsSignUp(false, '2015-10-21 12:34:56', array(
            array('country' => 'US', 'region' => 'AL'),
            array('country' => 'FR')
        ));

        $expected = array('1', '3');
        $this->assertCustomerIdsEquals($expected, $results);
    }

    public function testGetBirthdayClientsSignupShoulReturnClientInCorrectRegionsAndCountriesAndNotHavingAddresses()
    {
        $results = ShopymindClient_Callback::getBirthdayClientsSignUp(false, '2015-10-21 12:34:56', array(
            array('country' => 'US', 'region' => 'AL'),
            false
        ));

        $expected = array('3', '6');
        $this->assertCustomerIdsEquals($expected, $results);
    }

    public function testGetBirthdayClientsSignupShoulReturnClientCountCorrespondingToFilters()
    {
        $results = ShopymindClient_Callback::getBirthdayClientsSignUp(false, '2015-10-21 12:34:56', array(array('country' => 'FR'), array('country' => 'US')), true);

        $expected = 3;
        $this->assertEquals($expected, $results['count']);
    }

    public function testGetBirthdayClientsSignupShoulReturnClientAccordingToScope()
    {
        $results = ShopymindClient_Callback::getBirthdayClientsSignUp('store-2', '2015-10-27 12:34:56', array(array('country' => 'FR'), array('country' => 'US')));

        $expected = array('5');
        $this->assertCustomerIdsEquals($expected, $results);
    }

    private function assertCustomerIdsEquals($expected, $results)
    {
        $customerIds = array_map(
            function ($customer) {
                return $customer['customer']['id_customer'];
            },
            $results
        );
        $this->assertEquals($expected, $customerIds);
    }
}
