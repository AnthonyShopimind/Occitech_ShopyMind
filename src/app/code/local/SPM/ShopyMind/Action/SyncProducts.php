<?php

class SPM_ShopyMind_Action_SyncProducts implements SPM_ShopyMind_Interface_Action
{
    private $params;
    private $productDataMapper;

    public function __construct(SPM_ShopyMind_Model_Scope $scope, $start, $limit, $lastUpdate, $productId = false, $justCount = false)
    {
        $this->params = array(
            'scope' => $scope,
            'start' => $start,
            'limit' => $limit,
            'lastUpdate' => $lastUpdate,
            'productId' => $productId,
            'justCount' => $justCount
        );
    }

    public function setProductDataMapper(SPM_ShopyMind_DataMapper_Product $productDataMapper = null)
    {
        $this->productDataMapper = $productDataMapper;
    }

    private function getProductDataMapper()
    {
        if (is_null($this->productDataMapper)) {
            $this->productDataMapper = new SPM_ShopyMind_DataMapper_Product();
        }

        return $this->productDataMapper;
    }

    public function process()
    {
        $storeIds = $this->params['scope']->storeIds();
        $return = array();
        /** @var  $appEmulation Mage_Core_Model_App_Emulation */
        $appEmulation = Mage::getSingleton('core/app_emulation');
        foreach ($storeIds AS $storeId) {
            $initialScope = $this->params['scope'];
            $scope = SPM_ShopyMind_Model_Scope::fromShopymindId('store-'.$storeId);
            //echo $scope->getLang();
            $this->params['scope'] = $scope;

            $emulatedEnvironment = $appEmulation->startEnvironmentEmulation($storeId);

            $productCollection = $this->retrieveProducts($storeId);
            if ($this->params['justCount']) {
                return $productCollection->count();
            }

            $formatter = new SPM_ShopyMind_DataMapper_Pipeline(array(
                array($this->getProductDataMapper(), 'format'),
                SPM_ShopyMind_DataMapper_Scope::makeScopeEnricher($this->params['scope']),
            ));
            $currentReturn = $formatter->format(iterator_to_array($productCollection));
            foreach ($currentReturn as $product) {
                $product_key = $product['id_product'].'-'.$product['lang'];
                if(!isset($return[$product_key])) $return[$product_key] = $product;
            }
            $appEmulation->stopEnvironmentEmulation($emulatedEnvironment);
            $this->params['scope'] = $initialScope;
        }
        return $return;
    }

    public function retrieveProducts($storeId = false)
    {
        $helper = Mage::helper('shopymind');
        $emulatedEnvironment = $helper->startEmulatingScope($this->params['scope']);

        $productCollection = Mage::getModel('catalog/product')->getCollection();
        $this->params['scope']->restrictProductCollection($productCollection);
        $productCollection->addAttributeToSelect('*');
        $productCollection->addFieldToFilter('visibility', array('in' => Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds()));
        $productCollection->setFlag('require_stock_items', true);

        if ($this->params['productId']) {
            $conditions = is_array($this->params['productId']) ? array('in' => $this->params['productId']) : array('eq' => (int)$this->params['productId']);
            $productCollection->addFieldToFilter('entity_id', $conditions);
        }

        if ($this->params['lastUpdate']) {
            $productCollection->addFieldToFilter('updated_at', array('gt' => $this->params['lastUpdate']));
        }

        $offset = !empty($this->params['start']) ? (int) $this->params['start'] : null;
        $limit = !empty($this->params['limit']) ? (int) $this->params['limit'] : null;

        $productCollection->getSelect()->limit($limit, $offset);
        $newCollection = array();

        if($storeId) {
            foreach ($productCollection as $key=>$product) {
                if(!in_array($storeId,$product->getStoreIds())){
                    $productCollection->removeItemByKey($key);
                }
            }
        }
        $helper->stopEmulation($emulatedEnvironment);
        return $productCollection;
    }

}
