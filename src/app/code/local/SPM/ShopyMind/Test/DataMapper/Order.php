<?php

/**
 * @group dataMappers
 * @loadSharedFixture
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_DataMapper_Order extends EcomDev_PHPUnit_Test_Case
{
    public $SUT;

    public function setup() {
        parent::setup();
        $this->SUT = new SPM_ShopyMind_DataMapper_Order();
    }

    public function testFormatOrderData()
    {
        $order = Mage::getModel('sales/order')->load(1);
        $quote = Mage::getModel('sales/quote')->load(1);
        $order->setQuote($quote);

        $expected = array(
            'shop_id_shop' => 1,
            'order_is_confirm' => true,
            'order_reference' => '',
            'id_cart' => 1,
            'id_status' => 'pending',
            'date_cart' => '2015-12-09 13:53:06',
            'id_order' => 100,
            'lang' => '',
            'amount' => 28.0300,
            'tax_rate' => 1.0000,
            'currency' => 'EUR',
            'date_order' => '2015-12-09 11:53:06',
            'voucher_used' => array(),
            'voucher_amount' => 0,
            'products' => array(),
            'customer' => array(),
            'shipping_number' => null,
        );

        $actual = $this->SUT->format($order, array(), null);
        $this->assertEquals($expected, $actual);
    }
}
