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
    }

    public function testIsDateOfBirthRequiredForModule()
    {
        $event = $this->generateObserver(array(), 'admin_system_config_changed_section_shopymind_configuration');
        $this->assertFalse($this->SUT->isDateOfBirthRequiredForModule($event));
    }

    public function testSaveShouldUpdateCustomerConfigWhenDateOfBirthIsRequired()
    {
        $Config = $this->getModelMock('core/config', array('saveConfig'));
        $Config->expects($this->once())
            ->method('saveConfig')
            ->with(
                $this->equalTo('customer/address/dob_show'),
                $this->equalTo(SPM_ShopyMind_Model_Observer::OPTIONAL_CUSTOMER_DOB),
                $this->equalTo('stores'),
                $this->equalTo('1')
            );

        $this->replaceByMock('model', 'core/config', $Config);
        $event = $this->generateObserver(array('store' => 'default'), 'admin_system_config_changed_section_shopymind_configuration');
        $this->SUT->adminSystemConfigChangedSectionShopymindConfiguration($event);
    }

    public function testSaveShouldUpdateCustomerDobAttributeRequirement()
    {
        $Entity = $this->getModelMock('eav/entity_setup', array('updateAttribute'), false, array('core_setup'));
        $Entity->expects($this->once())
            ->method('updateAttribute')
            ->with(
                $this->equalTo(1),
                $this->equalTo('dob'),
                $this->equalTo(
                    array(
                        'is_required' => 0,
                        'is_visible' => true,
                    )
                )
            );

        $this->replaceByMock('model', 'eav/entity_setup', $Entity);
        $event = $this->generateObserver(array('store' => 'default'), 'admin_system_config_changed_section_shopymind_configuration');
        $this->SUT->adminSystemConfigChangedSectionShopymindConfiguration($event);
    }
}