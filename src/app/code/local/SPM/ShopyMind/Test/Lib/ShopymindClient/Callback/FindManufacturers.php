<?php

/**
 * @loadSharedFixture
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_FindManufacturers extends EcomDev_PHPUnit_Test_Case
{

    const ENTITY_PRODUCT_TYPE = 4;

    const MANUFACTURER_ATTRIBUTE_CODE = 'manufacturer';

    public static function setUpBeforeClass()
    {
        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
        $setup->addAttributeOption(array(
            'attribute_id' => $setup->getAttributeId(self::ENTITY_PRODUCT_TYPE, self::MANUFACTURER_ATTRIBUTE_CODE),
            'value' => array(
                'renault' => array(
                    0 => 'Renault cars',
                    1 => 'Dacia',
                ),
                'peugeot' => array(
                    0 => 'Peugeot cars',
                    1 => 'Peugeot TM',
                ),
                'volvo' => array(
                    0 => 'Volvo cars'
                ),
            )
        ));
        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass()
    {
        $attribute = Mage::getModel('eav/config')->getAttribute(self::ENTITY_PRODUCT_TYPE, self::MANUFACTURER_ATTRIBUTE_CODE);
        $options = $attribute->getSource()->getAllOptions(false);

        $optionsToDelete = array();
        foreach ($options as $option) {
            $optionsToDelete['delete'][$option['value']] = true;
            $optionsToDelete['value'][$option['value']] = true;
        }

        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
        $setup->addAttributeOption($optionsToDelete);
        parent::tearDownAfterClass();
    }

    public function testAnEmptyArrayIsReturnedWhenNoManufacturersMatchInTheShop()
    {
        $result = ShopymindClient_Callback::findManufacturers(false, false, 'nobody');
        $this->assertSame(array(), $result);
    }

    public function testManufacturersAreOrderedByName()
    {
        $manufacturers = ShopymindClient_Callback::findManufacturers(false, false, 'cars');
        $values = array_map(function($manufacturer) { return $manufacturer['value']; }, $manufacturers);

        $expectedValues = array('Peugeot cars', 'Renault cars', 'Volvo cars');
        $this->assertEquals($expectedValues, $values);
    }

    public function testManufacturersCanBeFoundByStore()
    {
        $manufacturersInGeneralStore = ShopymindClient_Callback::findManufacturers(false, false, 'Daci');
        $manufacturersInSpecificStore = ShopymindClient_Callback::findManufacturers('website-1', false, 'Daci');

        $this->assertCount(0, $manufacturersInGeneralStore);
        $this->assertCount(1, $manufacturersInSpecificStore);
    }

    public function testNothingIsReturnedWhenTheSearchIs2CharsLong()
    {
        $result = ShopymindClient_Callback::findManufacturers(false, false, 'ca');
        $this->assertSame(array(), $result);
    }

}
