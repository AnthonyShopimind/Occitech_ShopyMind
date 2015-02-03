<?php

/**
 * @loadSharedFixture
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_GetContacts extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @loadFixture anInactiveUser
     */
    public function testGetContactsShouldReturnAllActiveCustomerWithoutFilters() {
        $result = ShopymindClient_Callback::getContacts(false, 0, false, '1970-01-01');
        $resultCustomerIds = array_map(function($customer) { return $customer['customer']['id_customer']; }, $result);

        $expected = array('1', '2');
        $this->assertEquals($expected, $resultCustomerIds);
    }

    /**
     * @loadFixture moreUsersForLimit
     */
    public function testGetContactsShouldReturnCustomersBeginningAtStartIndexIfLimitIsSet() {
        $result = ShopymindClient_Callback::getContacts('store-1', 4, 10, '1970-01-01');
        $resultCustomerIds = array_map(function($customer) { return $customer['customer']['id_customer']; }, $result);

        $expected = array('5');
        $this->assertEquals($expected, $resultCustomerIds);
    }

    /**
     * @loadFixture moreUsersForLimit
     */
    public function testGetContactsShouldIgnoreStartParamIfLimitIsFalse() {
        $result = ShopymindClient_Callback::getContacts('store-1', 4, false, '1970-01-01');
        $resultCustomerIds = array_map(function($customer) { return $customer['customer']['id_customer']; }, $result);

        $expected = array('1', '2', '3', '4', '5');
        $this->assertEquals($expected, $resultCustomerIds);
    }

    /**
     * @loadFixture moreUsersForLimit
     */
    public function testGetContactsShouldReturnCustomersLimitedByLimit() {
        $result = ShopymindClient_Callback::getContacts('store-1', 0, 2, '1970-01-01');
        $resultCustomerIds = array_map(function($customer) { return $customer['customer']['id_customer']; }, $result);

        $expected = array('1', '2');
        $this->assertEquals($expected, $resultCustomerIds);
    }

    /**
     * @loadFixture aStore2User
     */
    public function testGetContactsShouldReturnCustomersForStoreId() {
        $result = ShopymindClient_Callback::getContacts('store-2', 0, false, '1970-01-01');
        $resultCustomerIds = array_map(function($customer) { return $customer['customer']['id_customer']; }, $result);

        $expected = array('3');
        $this->assertEquals($expected, $resultCustomerIds);
    }

    public function testGetContactsShouldReturnEmptyArryIfNoCustomersMatchFilters() {
        $result = ShopymindClient_Callback::getContacts('store-3', 0, false, '1970-01-01');
        $resultCustomerIds = array_map(function($customer) { return $customer['customer']['id_customer']; }, $result);

        $this->assertEmpty($resultCustomerIds);
    }

    public function testGetContactsShouldReturnCustomerUpdatedAfterLastUpdate() {
        $result = ShopymindClient_Callback::getContacts('website-1', 0, false, "2015-01-20");
        $resultCustomerIds = array_map(function($customer) { return $customer['customer']['id_customer']; }, $result);

        $expected = array('2');
        $this->assertEquals($expected, $resultCustomerIds);
    }

    public function testGetContactsShouldReturnCustomerCountIfJustCount() {
        $result = ShopymindClient_Callback::getContacts('store-1', 0, false, '1970-01-01', true);

        $expected = 4;
        $this->assertEquals($expected, $result['count']);
    }
}
