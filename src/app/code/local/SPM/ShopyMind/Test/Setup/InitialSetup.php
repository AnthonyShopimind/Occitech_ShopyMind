<?php

class InitialSetup extends EcomDev_PHPUnit_Test_Case
{

    /**
     * Hack to prevent as much as possible "undefined variable $enabledTable" kind of
     * errors du to initial magento setups messing with classes internal states
     *
     * Run it separately from the main test suite to allow "preload" a stable database
     *
     * @group setup
     */
    public function testItWillRunAllMagentoSetupsWithoutInternalCacheMessingThingsUp()
    {
        $this->assertTrue(
            true,
            'This prevent incorrect cache states due to code from Magento setups. For instance \Mage_Catalog_Model_Resource_Category_Indexer_Product::$_storesInfo'
        );
    }
}
