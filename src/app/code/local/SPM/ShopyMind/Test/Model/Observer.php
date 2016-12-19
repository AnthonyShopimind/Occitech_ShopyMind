<?php

/**
 * @loadSharedFixture
 * @group observer
 */
class SPM_ShopyMind_Test_Model_Observer extends EcomDev_PHPUnit_Test_Case_Config
{
    private $scope;
    private $SUT;

    protected function setUp()
    {
        parent::setUp();

        $this->scope = $this->getMock('SPM_ShopyMind_Model_Scope', array('getConfig', 'saveConfig'), array(), '', false);

        $this->SUT = $this->getModelMock('shopymind/observer', array('dispatchToShopyMind'));
        $this->replaceByMock('model', 'shopymind/observer', $this->SUT);
        $this->mockGetUrlContacts();
    }

    public function testIsDateOfBirthRequiredForModuleOnGlobalScope()
    {
        $scope = SPM_ShopyMind_Model_Scope::fromMagentoCodes(null, null);
        $this->assertFalse($this->SUT->isDateOfBirthRequiredForModule($scope));
    }

    public function testIsDateOfBirthRequiredForModuleWithLegacyConfig()
    {
        $this->givenConfiguration('shopymind/configuration/birthrequired', 'yes');
        $this->assertTrue($this->SUT->isDateOfBirthRequiredForModule($this->scope));
    }

    public function testSaveShouldUpdateCustomerConfigWithDateOfBirthRequiredItIsRequiredForScope()
    {
        $this->givenConfiguration('shopymind/configuration/birthrequired', 'yes');
        $this->expectConfigurationChanged(
            'customer/address/dob_show',
            SPM_ShopyMind_Model_Observer::REQUIRED_CUSTOMER_DOB
        );
        $this->SUT->updateDateOfBirthCustomerAttributeFrom($this->scope);
    }

    public function testSaveShouldUpdateCustomerConfigWithDateOfBirthNotRequiredItIsOptionalForScope()
    {
        $this->givenConfiguration('shopymind/configuration/birthrequired', 'no');
        $this->expectConfigurationChanged(
            'customer/address/dob_show',
            SPM_ShopyMind_Model_Observer::OPTIONAL_CUSTOMER_DOB
        );
        $this->SUT->updateDateOfBirthCustomerAttributeFrom($this->scope);
    }

    public function testSaveShouldUpdateCustomerDobAttributeRequirementWhenRequired()
    {
        $this->givenConfiguration('shopymind/configuration/birthrequired', 'yes');
        $this->expectsAttributeIsUpdatedWith(1, 'dob', array('is_required' => 1, 'is_visible' => true));

        $this->SUT->updateDateOfBirthCustomerAttributeFrom($this->scope);
    }

    public function testSaveShouldUpdateCustomerDobAttributeRequirementWhenOptional()
    {
        $this->givenConfiguration('shopymind/configuration/birthrequired', 'no');
        $this->expectsAttributeIsUpdatedWith(1, 'dob', array('is_required' => 0, 'is_visible' => true));

        $this->SUT->updateDateOfBirthCustomerAttributeFrom($this->scope);
    }

    public function testIsMultiStore()
    {
        $this->assertTrue($this->SUT->isMultiStore());
    }

    public function testIsMultiStoreShouldDoNotTakeIntoAccountInactiveStores()
    {
        Mage::app()->getStore(2)->setIsActive(0)->save();
        $isMultiStore = $this->SUT->isMultiStore();
        Mage::app()->getStore(2)->setIsActive(1)->save();

        $this->assertFalse($isMultiStore);
    }

    public function testStoreViewsWithDifferentLanguagesIsNotMultiStore()
    {
        $initialValue = Mage::app()->getStore(1)->getConfig('general/locale/code');
        Mage::app()->getStore(1)->setConfig('general/locale/code', 'fr_FR');
        $isMultiStore = $this->SUT->isMultiStore();
        Mage::app()->getStore(1)->setConfig('general/locale/code', $initialValue);

        $this->assertFalse($isMultiStore);
    }

    public function testSendCorrectStoreInformationsToShopyMind()
    {
        $this->SUT->expects($this->once())
            ->method('dispatchToShopyMind')
            ->with(
                'foo-bar',
                'bar',
                'en',
                'GBP',
                'http://magento.local/contacts/contacts/index',
                '+33102030405',
                'Europe/London',
                0,
                'default-0'
            );

        $scope = SPM_ShopyMind_Model_Scope::fromMagentoCodes(null, null);
        $this->SUT->sendInformationToShopyMindFor($scope);
    }

    private function expectsAttributeIsUpdatedWith($entityTypeId, $attributeCode, $attributeValue)
    {
        $Entity = $this->getModelMock('eav/entity_setup', array('updateAttribute'), false, array('core_setup'));
        $Entity->expects($this->once())
            ->method('updateAttribute')
            ->with(
                $this->equalTo($entityTypeId),
                $this->equalTo($attributeCode),
                $this->equalTo($attributeValue)
            );

        $this->replaceByMock('model', 'eav/entity_setup', $Entity);
    }

    private function mockGetUrlContacts()
    {
        $MockedUrl =  $this->mockModel('core/url', array('getUrl'));
        $MockedUrl->expects($this->any())
            ->method('getUrl')
            ->with($this->equalTo('contacts'))
            ->will($this->returnValue('http://magento.local/contacts/contacts/index'));

        $this->replaceByMock('model', 'core/url', $MockedUrl);
    }

    private function givenConfiguration($path, $value)
    {
        $this->scope->expects($this->any())->method('getConfig')
            ->with($path)
            ->will($this->returnValue($value));
    }

    private function expectConfigurationChanged($path, $value)
    {
        $this->scope->expects($this->once())->method('saveConfig')
            ->with($path, $value);
    }

    public function testNeededEventsAreListenedWithCorrectMethods()
    {
        $events = array(
            'catalog_product_save_commit_after' => 'saveProduct',
            'catalog_product_delete_after_done' => 'deleteProduct',
            'catalog_category_save_commit_after' => 'saveProductCategory',
            'catalog_category_delete_after' => 'deleteProductCategory',
            'customer_save_commit_after' => 'saveCustomer',
            'customer_address_save_commit_after' => 'saveCustomerAddress',
            'customer_address_delete_after' => 'saveCustomerAddress',
            'customer_delete_after' => 'deleteCustomer',
        );

        foreach($events as $event => $method) {
            $this->assertEventObserverDefined('global', $event, 'shopymind/observer', $method);
        }
    }
}
