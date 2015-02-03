<?php

/**
 * @loadSharedFixture
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_GetContacts extends EcomDev_PHPUnit_Test_Case
{
    public function testGetContactsShouldReturnAllActiveCustomerWithoutFilters() {
        $result = ShopymindClient_Callback::getContacts(1, 0, false, '1970-01-01');
        $resultCustomerIds = array_map(function($customer) { return $customer['customer']['id_customer']; }, $result);

        $expected = array('1', '2', '4', '5', '6');
        $this->assertEquals($expected, $resultCustomerIds);
    }

    public function testGetContactsShouldReturnCustomersBeginningAtStartIndexIfLimitIsSet() {
        $result = ShopymindClient_Callback::getContacts(1, 4, 10, '1970-01-01');
        $resultCustomerIds = array_map(function($customer) { return $customer['customer']['id_customer']; }, $result);

        $expected = array('6');
        $this->assertEquals($expected, $resultCustomerIds);
    }

    public function testGetContactsShouldIgnoreStartParamIfLimitIsFalse() {
        $result = ShopymindClient_Callback::getContacts(1, 4, false, '1970-01-01');
        $resultCustomerIds = array_map(function($customer) { return $customer['customer']['id_customer']; }, $result);

        $expected = array('1', '2', '4', '5', '6');
        $this->assertEquals($expected, $resultCustomerIds);
    }

    public function testGetContactsShouldReturnCustomersLimitedByLimit() {
        $result = ShopymindClient_Callback::getContacts(1, 0, 4, '1970-01-01');
        $resultCustomerIds = array_map(function($customer) { return $customer['customer']['id_customer']; }, $result);

        $expected = array('1', '2', '4', '5');
        $this->assertEquals($expected, $resultCustomerIds);
    }

    public function testGetContactsShouldReturnCustomersForStoreId() {
        $result = ShopymindClient_Callback::getContacts(2, 0, false, '1970-01-01');
        $resultCustomerIds = array_map(function($customer) { return $customer['customer']['id_customer']; }, $result);

        $expected = array('7');
        $this->assertEquals($expected, $resultCustomerIds);
    }

    public function testGetContactsShouldReturnEmptyArryIfNoCustomersMatchFilters() {
        $result = ShopymindClient_Callback::getContacts(3, 0, false, '1970-01-01');
        $resultCustomerIds = array_map(function($customer) { return $customer['customer']['id_customer']; }, $result);

        $this->assertEmpty($resultCustomerIds);
    }
    public function testGetContactsShouldReturnCustomerUpdatedAfterLastUpdate() {
        $result = ShopymindClient_Callback::getContacts(1, 0, false, "2015-01-20");
        $resultCustomerIds = array_map(function($customer) { return $customer['customer']['id_customer']; }, $result);

        $expected = array('5');
        $this->assertEquals($expected, $resultCustomerIds);
    }

    public function testGetContactsShouldReturnCustomerCountIfJustCount() {
        $result = ShopymindClient_Callback::getContacts(1, 0, false, '1970-01-01', true);

        $expected = 5;
        $this->assertEquals($expected, $result['count']);
    }
}
