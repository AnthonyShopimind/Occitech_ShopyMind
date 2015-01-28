<?php

class SPM_ShopyMind_Test_Config_Main extends EcomDev_PHPUnit_Test_Case_Config
{
	public function testItObservesOrdersPayment()
	{
		$this->assertEventObserverDefined(
			'global',
			'sales_order_invoice_pay',
			'shopymind/observer',
			'newOrderObserver'
		);
	}
}
