<?php

/**
 * @group mine
 * @loadSharedFixture
 */
class SPM_ShopyMind_Test_Helper_Data extends EcomDev_PHPUnit_Test_Case_Config
{
    public $SUT;

    protected function setUp()
    {
        parent::setUp();
        $this->SUT = Mage::helper('shopymind');
    }

//    public function testFormatQuoteItem()
//    {
//        $quote = Mage::getModel('sales/quote')->load(1);
//
//        $expected = array(
//            'id_product' => $quoteItem->getId(),
//            'id_manufacturer' => 11,
//            'qty' => 2,
//            'price' => 120,
//        );
//
//        $actual = $this->SUT->formatQuoteItem($quoteItem);
//
//        $this->assertEquals($expected, $actual);
//    }
}
