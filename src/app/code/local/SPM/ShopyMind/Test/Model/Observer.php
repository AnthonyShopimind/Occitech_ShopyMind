<?php

/**
 * @loadSharedFixture
 */
class SPM_ShopyMind_Test_Model_Observer extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @var SPM_ShopyMind_Model_Observer
     */
    private $SUT;

    protected function setUp()
    {
        parent::setUp();

        $this->SUT = $this->getModelMock('shopymind/observer', array('dispatchToShopyMind'));
        $this->replaceByMock('model', 'shopymind/observer', $this->SUT);
        $this->mockGetUrlContacts();
    }

    public function testIsDateOfBirthRequiredForModuleOnGlobalScope()
    {
        $event = $this->generateShopymindConfigurationChangedEvent();
        $this->assertFalse($this->SUT->isDateOfBirthRequiredForModule($event));
    }

    public function testIsDateOfBirthRequiredForModuleWithLegacyConfig()
    {
        Mage::app()->getStore(0)->setConfig('shopymind/configuration/birthrequired', 'no');
        $event = $this->generateShopymindConfigurationChangedEvent();
        $this->assertFalse($this->SUT->isDateOfBirthRequiredForModule($event));
    }
    public function testIsDateOfBirthRequiredForModuleOnStoreScope()
    {
        $event = $this->generateShopymindConfigurationChangedEvent('second_website_store');
        $this->assertTrue($this->SUT->isDateOfBirthRequiredForModule($event));
    }

    public function testSaveShouldUpdateCustomerConfigWithDateOfBirthRequirementOnStoreScope()
    {
        $this->expectsConfigIsSavedWith('customer/address/dob_show', SPM_ShopyMind_Model_Observer::REQUIRED_CUSTOMER_DOB, 'stores', 2);
        $event = $this->generateShopymindConfigurationChangedEvent('second_website_store');
        $this->SUT->adminSystemConfigChangedSectionShopymindConfiguration($event);
    }

    public function testSaveShouldUpdateCustomerDobAttributeRequirementOnStoreScope()
    {
        $this->expectsAttributeIsUpdatedWith(1, 'dob', array('is_required' => 1, 'is_visible' => true));

        $event = $this->generateShopymindConfigurationChangedEvent('second_website_store');
        $this->SUT->adminSystemConfigChangedSectionShopymindConfiguration($event);
    }

    public function testSaveShouldUpdateCustomerConfigWithDateOfBirthRequirementOnGlobalScope()
    {
        $this->expectsConfigIsSavedWith('customer/address/dob_show', SPM_ShopyMind_Model_Observer::OPTIONAL_CUSTOMER_DOB, 'default', 0);
        $event = $this->generateShopymindConfigurationChangedEvent();
        $this->SUT->adminSystemConfigChangedSectionShopymindConfiguration($event);
    }

    public function testSaveShouldUpdateCustomerDobAttributeRequirementOnGlobalScope()
    {
        $this->expectsAttributeIsUpdatedWith(1, 'dob', array('is_required' => 0, 'is_visible' => true));

        $event = $this->generateShopymindConfigurationChangedEvent();
        $this->SUT->adminSystemConfigChangedSectionShopymindConfiguration($event);
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
                true,
                1
            );

        $this->SUT->sendInformationsForShopyMindForStore('default');
    }

    public function testSendDefaultStoreInformationsToShopyMindWhenNoStoreCodePassed()
    {
        $this->SUT->expects($this->once())
            ->method('dispatchToShopyMind')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                1
            );

        $this->SUT->sendInformationsForShopyMindForStore();
    }

    private function generateShopymindConfigurationChangedEvent($storeCode = null)
    {
        return $this->generateObserver(array('store' => $storeCode), 'admin_system_config_changed_section_shopymind_configuration');
    }

    private function expectsConfigIsSavedWith($configPath, $configValue, $scope, $scopeId)
    {
        $Config = $this->getModelMock('core/config', array('saveConfig'));
        $Config->expects($this->once())
            ->method('saveConfig')
            ->with(
                $this->equalTo($configPath),
                $this->equalTo($configValue),
                $this->equalTo($scope),
                $this->equalTo($scopeId)
            );

        $this->replaceByMock('model', 'core/config', $Config);
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
}
