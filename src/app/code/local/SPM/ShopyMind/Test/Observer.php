<?php

class SPM_ShopyMind_Test_Observer
{
    public function beforeTestStart()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            $store = Mage::getModel('core/store')->load(1);
            if (!$store->isEmpty()) {
                $this->_setStore($store->getCode());
            }
        }

        if ($this->_shouldRunWithFlatCatalog()) {
            $this->_enableFlatCatalog();
            $this->_reindexFlatCatalog();
        }
    }

    protected function _setStoreConfig($path, $value)
    {
        Mage::app()->getStore()
            ->setConfig($path, $value);
    }

    protected function _shouldRunWithFlatCatalog()
    {
        return getenv('USE_FLAT_CATALOG');
    }

    protected function _enableFlatCatalog()
    {
        $this->_setStoreConfig('catalog/frontend/flat_catalog_product', '1');
        $this->_setStoreConfig('catalog/frontend/flat_catalog_category', '1');
    }

    protected function _reindexFlatCatalog()
    {
        Mage::getResourceModel('catalog/product_flat_indexer')->rebuild();
        Mage::getResourceModel('catalog/category_flat')->rebuild();
    }

    protected function _setStore($store)
    {
        EcomDev_PHPUnit_Test_Case_Util::setCurrentStore($store);
    }
}
