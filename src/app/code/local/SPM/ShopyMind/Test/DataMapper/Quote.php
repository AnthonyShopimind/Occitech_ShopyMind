<?php

/**
 * @group dataMappers
 * @loadSharedFixture
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_DataMapper_Quote extends EcomDev_PHPUnit_Test_Case
{
    public $SUT;

    public function setup() {
        parent::setup();
        $this->SUT = new SPM_ShopyMind_DataMapper_Quote();
    }

    public function testFormatQuoteData()
    {
        $quote = Mage::getModel('sales/quote')->load(1);

        $expected = array(
            'id_customer' => 1,
            'id_cart' => 1,
            'date_add' => '2015-12-09 11:53:06',
            'date_upd' => '2015-12-09 13:53:06',
            'amount' => 120,
            'tax_rate' => 1,
            'currency' => 'EUR',
            'voucher_used' => array(),
            'voucher_amount' => 0,
            'products' => array(),
        );

        $actual = $this->SUT->format($quote);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @loadFixture coupon
     */
    public function testFormatQuoteDataWithCoupon()
    {
        $quote = Mage::getModel('sales/quote')->load(2);

        $expected = array(
            'id_customer' => 1,
            'id_cart' => 2,
            'date_add' => '2015-12-09 11:53:06',
            'date_upd' => '2015-12-09 13:53:06',
            'amount' => 100,
            'tax_rate' => 1,
            'currency' => 'EUR',
            'voucher_used' => array('ML9DUOQZLISN'),
            'voucher_amount' => -20,
            'products' => array(),
        );

        $actual = $this->SUT->format($quote);
        $this->assertEquals($expected, $actual);
    }
}
