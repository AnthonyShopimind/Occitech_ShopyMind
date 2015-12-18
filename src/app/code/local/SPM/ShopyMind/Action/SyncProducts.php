<?php

class SPM_ShopyMind_Action_SyncProducts implements SPM_ShopyMind_Interface_Action
{
    private $params;
    private $dataMapper;

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

    public function setDataMapper(SPM_ShopyMind_DataMapper_Product $dataMapper = null)
    {
        $this->dataMapper = $dataMapper;
    }

    private function getDataMapper()
    {
        if (is_null($this->dataMapper)) {
            $this->dataMapper = new SPM_ShopyMind_DataMapper_Product();
        }

        return $this->dataMapper;
    }

    public function process()
    {
        $productCollection = $this->retrieveProducts();

        if ($this->params['justCount']) {
            return $productCollection->count();
        }

        $scopeData = $this->getScopedRelatedInformations($this->params['scope']);
        $scopeDataEnricher = function($productData) use ($scopeData) {
           return array_merge($productData, $scopeData);
        };

        return array_map($scopeDataEnricher, array_map(
            array($this->getDataMapper(), 'format'),
            iterator_to_array($productCollection)
        ));
    }

    public function getScopedRelatedInformations(SPM_ShopyMind_Model_Scope $scope)
    {
        return array(
            'shop_id_shop' => $scope->getId(),
            'lang' => $scope->getLang(),
            'currency' => $scope->currencyCode()
        );
    }

    public function retrieveProducts()
    {
        $storeIds = $this->params['scope']->storeIds();
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $emulatedEnvironment = $appEmulation->startEnvironmentEmulation($storeIds[0]);

        $productCollection = Mage::getModel('catalog/product')->getCollection();
        $this->params['scope']->restrictProductCollection($productCollection);
        $productCollection->addAttributeToSelect('*');

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

        $appEmulation->stopEnvironmentEmulation($emulatedEnvironment);
        return $productCollection;
    }
}
