<?php

/**
 * @loadSharedFixture
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_GetContacts extends EcomDev_PHPUnit_Test_Case
{
    public function testGetContactsShouldReturnAllActiveCustomerWithoutFilters() {
        $result = ShopymindClient_Callback::getContacts(1, 0, false, '2970-01-01');
        $resultCustomerIds = array_map(function($customer) { return $customer['customer']['id_customer']; }, $result);

        $expected = array('1', '2', '4', '5', '6');
        $this->assertEquals($expected, $resultCustomerIds);
    }

    public function testGetContactsShouldReturnCustomersBeginningAtStartIndexIfLimitIsSet() {
        $result = ShopymindClient_Callback::getContacts(1, 4, 10, null);
        $resultCustomerIds = array_map(function($customer) { return $customer['customer']['id_customer']; }, $result);

        $expected = array('6');
        $this->assertEquals($expected, $resultCustomerIds);
    }

    public function testGetContactsShouldIgnoreStartParamIfLimitIsFalse() {
        $result = ShopymindClient_Callback::getContacts(1, 4, false, null);
        $resultCustomerIds = array_map(function($customer) { return $customer['customer']['id_customer']; }, $result);

        $expected = array('1', '2', '4', '5', '6');
        $this->assertEquals($expected, $resultCustomerIds);
    }

    public function testGetContactsShouldReturnCustomersLimitedByLimit() {
        $result = ShopymindClient_Callback::getContacts(1, 0, 4, null);
        $resultCustomerIds = array_map(function($customer) { return $customer['customer']['id_customer']; }, $result);

        $expected = array('1', '2', '4', '5');
        $this->assertEquals($expected, $resultCustomerIds);
    }

    public function testGetContactsShouldReturnCustomersForStoreId() {
        $result = ShopymindClient_Callback::getContacts(2, 0, false, null);
        $resultCustomerIds = array_map(function($customer) { return $customer['customer']['id_customer']; }, $result);

        $expected = array('7');
        $this->assertEquals($expected, $resultCustomerIds);
    }

    public function testGetContactsShouldReturnEmptyArryIfNoCustomersMatchFilters() {
        $result = ShopymindClient_Callback::getContacts(3, 0, false, null);
        $resultCustomerIds = array_map(function($customer) { return $customer['customer']['id_customer']; }, $result);

        $expected = array();
        $this->assertEquals($expected, $resultCustomerIds);
    }
}
