<?php

class SPM_ShopyMind_Test_Config_Main extends EcomDev_PHPUnit_Test_Case_Config
{
    public function testItObservesOrdersPayment()
    {
        $this->assertEventObserverDefined(
            'global',
            'sales_order_save_commit_after',
            'shopymind/observer',
            'orderUpdateObserver'
        );
    }

    public function testItObservesAdminSystemConfigChangedShopymindConfiguration()
    {
        $this->assertEventObserverDefined(
            'global',
            'admin_system_config_changed_section_shopymind_configuration',
            'shopymind/observer',
            'adminSystemConfigChangedSectionShopymindConfiguration'
        );
    }
}
