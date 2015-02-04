<?php

/**
 * Value object for Magento scope managed in a Shopymind account
 * Allows to map which store ids are related to a shopymind id
 */
class SPM_ShopyMind_Model_Scope
{
    private $id;
    private $scope;

    const SCOPE_DEFAULT = 'default';
    const SCOPE_WEBSITE = 'website';
    const SCOPE_STORE = 'store';

    private function __construct($id, $scope)
    {
        $this->id = $id;
        $this->scope = $scope;
    }

    public static function fromShopymindId($shopymindId)
    {
        if (empty($shopymindId)) {
            $id = 0;
            $scope = self::SCOPE_DEFAULT;
        } else {
            list($scope, $id) = explode('-', $shopymindId);
        }
        return new self($id, $scope);
    }

    public function stores()
    {
        $stores = array_filter(Mage::app()->getStores(), array($this, 'isInScope'));
        return array_values($stores);
    }

    private function isInScope(Mage_Core_Model_Store $store)
    {
        $inScope = $store->getIsActive();
        if ($this->scope === self::SCOPE_STORE) {
            $inScope = $inScope && $store->getId() == $this->id;
        } elseif ($this->scope === self::SCOPE_WEBSITE) {
            $inScope = $inScope && $store->getWebsiteId() == $this->id;
        }
        return $inScope;
    }

    public function storeIds()
    {
        return array_map(function($store) {
            return $store->getId();
        }, $this->stores());
    }

    public function restrictCollection(Varien_Data_Collection_Db $collection, $storeIdField = 'store_id')
    {
        $collection->addFieldToFilter($storeIdField, array(
            'in' => $this->storeIds()
        ));
    }

}
