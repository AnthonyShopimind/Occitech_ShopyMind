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
        $productCollection = $this->retrieveProducts();
        if ($this->params['justCount']) {
            return $productCollection->count();
        }

        $formatter = new SPM_ShopyMind_DataMapper_Pipeline(array(
            array($this->getProductDataMapper(), 'format'),
            SPM_ShopyMind_DataMapper_Scope::makeScopeEnricher($this->params['scope']),
        ));
        return $formatter->format(iterator_to_array($productCollection));
    }

    public function retrieveProducts()
    {
        $helper = Mage::helper('shopymind');
        $emulatedEnvironment = $helper->startEmulatingScope($this->params['scope']);

        $productCollection = Mage::getModel('catalog/product')->getCollection();
        $this->params['scope']->restrictProductCollection($productCollection);
        $productCollection->addAttributeToSelect('*');
        $productCollection->addFieldToFilter('visibility', array('in', Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds()));

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
        $helper->stopEmulation($emulatedEnvironment);
        return $productCollection;
    }

}
