<?php

/**
 * @loadSharedFixture
 */
class SPM_ShopyMind_Test_Model_Observer extends EcomDev_PHPUnit_Test_Case
{
    private $SUT;

    protected function setUp()
    {
        parent::setUp();
        $this->SUT = Mage::getModel('shopymind/observer');
        $this->setConfigStores();
    }

    /**
     * @see http://www.wenda.io/questions/2656587/ecomdev-phpunit-fixture-for-website-specific-configuration.html
     */
    private function setConfigStores()
    {
        Mage::app()->getStore(0)
            ->setConfig('customer/address/dob_show', 'opt')
            ->setConfig('shopymind/configuration/birthrequired', 0);
        Mage::app()->getStore(2)
            ->setConfig('shopymind/configuration/birthrequired', 1);
    }

    public function testIsDateOfBirthRequiredForModuleOnGlobalScope()
    {
        $event = $this->generateObserver(array(), 'admin_system_config_changed_section_shopymind_configuration');
        $this->assertFalse($this->SUT->isDateOfBirthRequiredForModule($event));
    }

    public function testSaveShouldUpdateCustomerConfigWithDateOfBirthRequirementOnStoreScope()
    {
        $Config = $this->getModelMock('core/config', array('saveConfig'));
        $Config->expects($this->once())
            ->method('saveConfig')
            ->with(
                $this->equalTo('customer/address/dob_show'),
                $this->equalTo(SPM_ShopyMind_Model_Observer::REQUIRED_CUSTOMER_DOB),
                $this->equalTo('stores'),
                $this->equalTo('2')
            );

        $this->replaceByMock('model', 'core/config', $Config);
        $event = $this->generateObserver(array('store' => 'second_website_store'), 'admin_system_config_changed_section_shopymind_configuration');
        $this->SUT->adminSystemConfigChangedSectionShopymindConfiguration($event);
    }

    public function testSaveShouldUpdateCustomerDobAttributeRequirementOnStoreScope()
    {
        $Entity = $this->getModelMock('eav/entity_setup', array('updateAttribute'), false, array('core_setup'));
        $Entity->expects($this->once())
            ->method('updateAttribute')
            ->with(
                $this->equalTo(1),
                $this->equalTo('dob'),
                $this->equalTo(
                    array(
                        'is_required' => 1,
                        'is_visible' => true,
                    )
                )
            );

        $this->replaceByMock('model', 'eav/entity_setup', $Entity);
        $event = $this->generateObserver(array('store' => 'second_website_store'), 'admin_system_config_changed_section_shopymind_configuration');
        $this->SUT->adminSystemConfigChangedSectionShopymindConfiguration($event);
    }
}
