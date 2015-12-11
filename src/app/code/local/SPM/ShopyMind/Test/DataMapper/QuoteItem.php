<?php

/**
 * @group dataMappers
 * @loadSharedFixture
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_DataMapper_QuoteItem extends EcomDev_PHPUnit_Test_Case
{
    public $SUT;

    public function setup() {
        parent::setup();
        $this->SUT = new SPM_ShopyMind_DataMapper_QuoteItem();
    }

    public function testFormatProductItem()
    {
        $productItem = Mage::getModel('sales/quote_item')->load(1);
        $quote = Mage::getModel('sales/quote')->load(1);
        $productItem->setQuote($quote);

        $expected = array(
            'id_product' => 1,
            'id_combination' => 1,
            'id_manufacturer' => null,
            'price' => 3.5000,
            'qty' => 8.0000,
        );

        $actual = $this->SUT->format($productItem);
        $this->assertEquals($expected, $actual);
    }
}
