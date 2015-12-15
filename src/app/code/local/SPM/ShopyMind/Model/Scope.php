<?php

/**
 * Value object for Magento scope managed in a Shopymind account
 * Allows to map which store ids are related to a shopymind id
 */
class SPM_ShopyMind_Model_Scope
{
    private $id;
    private $scope;
    private $isoLangCode;

    const SCOPE_DEFAULT = 'default';
    const SCOPE_WEBSITE = 'website';
    const SCOPE_STORE = 'store';

    private function __construct($id, $scope, $isoLangCode)
    {
        $this->id = $id;
        $this->scope = $scope;
        $this->isoLangCode = $isoLangCode;
    }

    public static function buildUnrestricted()
    {
        return self::fromShopymindId('');
    }

    public static function fromShopymindId($shopymindId, $isoLangCode = false)
    {
        if (empty($shopymindId)) {
            $id = 0;
            $scope = self::SCOPE_DEFAULT;
        } else {
            list($scope, $id) = explode('-', $shopymindId);
        }
        return new self($id, $scope, $isoLangCode);
    }

    public static function fromMagentoCodes($websiteCode, $storeCode)
    {
        if (!is_null($storeCode)) {
            $id = Mage::getModel('core/store')->load($storeCode)->getId();
            $scope = self::SCOPE_STORE;
        } elseif (!is_null($websiteCode)) {
            $id = Mage::getModel('core/website')->load($websiteCode)->getId();
            $scope = self::SCOPE_WEBSITE;
        } else {
            $id = 0;
            $scope = self::SCOPE_DEFAULT;
        }
        return new self($id, $scope, false);
    }

    public static function fromOrder(Mage_Sales_Model_Order $order)
    {
        $id = $order->getStoreId();
        $scope = self::SCOPE_STORE;
        $isoLangCode = substr(Mage::getStoreConfig('general/locale/code', $id), 0, -3);

        return new self($id, $scope, $isoLangCode);
    }

    public static function fromRequest() {
        if(isset($_POST['shopIdShop']) && !empty($_POST['shopIdShop'])) {
            return self::fromShopymindId($_POST['shopIdShop']);
        }else {
            $currentStore = Mage::app()->getStore();
            if ($currentStore->isAdmin()) {
                $request = Mage::app()->getRequest();
                return self::fromMagentoCodes($request->getParam('website'), $request->getParam('store'));
            }
            return self::fromMagentoCodes($currentStore->getWebsite()->getCode(), $currentStore->getCode());
        }
    }

    public function shopyMindId()
    {
        return sprintf('%s-%s', $this->scope, $this->id);
    }

    public function stores()
    {
        $stores = array_filter(Mage::app()->getStores(), array($this, 'isInScope'));
        return array_values($stores);
    }

    public function restrictEavAttribute(Mage_Catalog_Model_Resource_Eav_Attribute $attribute)
    {
        if ($this->scope == self::SCOPE_DEFAULT && empty($this->isoLangCode)) {
            $attribute->setStoreId(0);
        } else {
            $storeIds = $this->storeIds();
            if (!empty($storeIds)) {
                $attribute->setStoreId($storeIds[0]);
            }
        }
    }

    private function isInScope(Mage_Core_Model_Store $store)
    {
        $inScope = $store->getIsActive();
        if ($this->scope === self::SCOPE_STORE) {
            $inScope = $inScope && $store->getId() == $this->id;
        } elseif ($this->scope === self::SCOPE_WEBSITE) {
            $inScope = $inScope && $store->getWebsiteId() == $this->id;
        }

        if (!empty($this->isoLangCode)) {
            $locale = Mage::getStoreConfig('general/locale/code', $store->getId());
            $inScope = $inScope && (stripos(substr($locale, 0, -3), $this->isoLangCode) === 0);
        }

        return $inScope;
    }

    public function storeIds()
    {
        return array_map(function($store) {
            return $store->getId();
        }, $this->stores());
    }

    public function getLang()
    {
        return $this->isoLangCode;
    }

    public function getId()
    {
        return $this->id;
    }

    public function currencyCode()
    {
        return Mage::app()->getStore($this->getId())->getCurrentCurrencyCode();
    }

    public function getConfig($path)
    {
        list($scope, $id) = $this->magentoConfigScopeValues();
        return Mage::getConfig()->getNode($path, $scope, $id);
    }

    public function saveConfig($path, $value)
    {
        list($scope, $id) = $this->magentoConfigScopeValues();
        return Mage::getConfig()->saveConfig($path, $value, $scope, $id);
    }

    public function restrictCollection(Varien_Data_Collection_Db $collection, $storeIdField = 'store_id')
    {
        $collection->addFieldToFilter($storeIdField, array(
            'in' => $this->storeIds()
        ));
    }

    public function restrictProductCollection(Varien_Data_Collection_Db $collection)
    {
        $this->guardAgainstInvalidProductCollection($collection);

        if ($this->scope == self::SCOPE_STORE) {
            $collection->addStoreFilter($this->id);
        } elseif ($this->scope == self::SCOPE_WEBSITE) {
            $collection->addWebsiteFilter($this->id);
        }
    }

    private function guardAgainstInvalidProductCollection($collection)
    {
        // This allows to ensure a correct collection is used with a wide compatibility range (1.5 -> 1.9)
        $isValid = (
            $collection instanceof Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
            || $collection instanceof Mage_Catalog_Model_Resource_Product_Collection
        );
        if (!$isValid) {
            throw new RuntimeException('Incorrect collection passed for filtering products by scope');
        }
    }

    public function restrictCategoryCollection(Varien_Data_Collection_Db $collection)
    {
        $this->guardAgainstInvalidCategoryCollection($collection);

        if ($this->scope === self::SCOPE_DEFAULT) {
            return;
        }

        if ($this->scope === self::SCOPE_STORE) {
            $stores = $this->stores();
            $rootCategoryId = $stores[0]->getRootCategoryId();
        } elseif ($this->scope === self::SCOPE_WEBSITE) {
            $rootCategoryId = Mage::app()->getWebsite($this->id)->getDefaultGroup()->getRootCategoryId();
        }

        $collection->addAttributeToFilter(array(
            array('attribute' => 'path', 'like' => "%/$rootCategoryId/%"),
            array('attribute' => 'path', 'like' => "%/$rootCategoryId%"),
        ));
    }

    private function guardAgainstInvalidCategoryCollection($collection)
    {
        // This allows to ensure a correct collection is used with a wide compatibility range (1.5 -> 1.9)
        $isValid = (
            $collection instanceof Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection
            || $collection instanceof Mage_Catalog_Model_Resource_Category_Collection
            || $collection instanceof Mage_Catalog_Model_Resource_Category_Flat_Collection
        );
        if (!$isValid) {
            throw new RuntimeException('Incorrect collection passed for filtering categories by scope');
        }
    }

    private function magentoConfigScopeValues()
    {
        // Scope must have a plural form since in Magento 1.5.x
        // the core_config_data.scope was an enum('default','websites','stores','config')
        if ($this->scope === self::SCOPE_WEBSITE) {
            return array('websites', (int) $this->id);
        } elseif ($this->scope === self::SCOPE_STORE) {
            return array('stores', (int) $this->id);
        }
        return array('default', 0);
    }

}
