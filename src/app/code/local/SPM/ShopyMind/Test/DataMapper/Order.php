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
            'order_reference' => 10001,
            'id_cart' => 1,
            'id_status' => 'pending',
            'date_cart' => '2015-12-09 13:53:06',
            'id_order' => 1,
            'lang' => 'fr',
            'amount' => 28.0300,
            'tax_rate' => 1.0000,
            'currency' => 'EUR',
            'date_order' => '2015-12-09 11:53:06',
            'voucher_used' => array(),
            'voucher_amount' => 0,
            'products' => array(),
            'customer' => array(),
            'shipping_number' => array(),
        );

        $actual = $this->SUT->format($order, array(), null);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @loadFixture coupon
     */
    public function testFormatOrderDataWithCoupon()
    {
        $order = Mage::getModel('sales/order')->load(2);
        $quote = Mage::getModel('sales/quote')->load(2);
        $order->setQuote($quote);

        $expected = array(
            'shop_id_shop' => 1,
            'order_is_confirm' => true,
            'order_reference' => 10002,
            'id_cart' => 2,
            'id_status' => 'pending',
            'date_cart' => '2015-12-09 13:53:06',
            'id_order' => 2,
            'lang' => 'fr',
            'amount' => 28.0300,
            'tax_rate' => 1.0000,
            'currency' => 'EUR',
            'date_order' => '2015-12-09 11:53:06',
            'voucher_used' => array("ML9DUOQZLISN"),
            'voucher_amount' => -10,
            'products' => array(),
            'customer' => array(),
            'shipping_number' => array(),
        );

        $actual = $this->SUT->format($order, array(), null);
        $this->assertEquals($expected, $actual);
    }
}
