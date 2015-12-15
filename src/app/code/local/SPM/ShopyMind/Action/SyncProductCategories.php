<?php

class SPM_ShopyMind_Action_SyncProductCategories implements SPM_ShopyMind_Interface_Action
{
    private $params;

    public function __construct(SPM_ShopyMind_Model_Scope $scope, $start, $limit, $lastUpdate, $categoryId, $justCount)
    {
        $this->params['scope'] = $scope;
        $this->params['start'] = $start;
        $this->params['limit'] = $limit;
        $this->params['lastUpdate'] = $lastUpdate;
        $this->params['categoryId'] = $categoryId;
        $this->params['justCount'] = $justCount;
    }

    public function process()
    {
        $storeIds = $this->params['scope']->storeIds();

        /** @var  $appEmulation Mage_Core_Model_App_Emulation */
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $emulatedEnvironment = $appEmulation->startEnvironmentEmulation($storeIds[0]);
        $categories = $this->retrieveCategories();

        if ($this->params['justCount']) {
            return $categories;
        }

        $Formatter = new SPM_ShopyMind_DataMapper_Category($this->params['scope']);
        $categories = array_map(array($Formatter, 'format'), iterator_to_array($categories->getIterator()));
        $appEmulation->stopEnvironmentEmulation($emulatedEnvironment);

        return $categories;
    }

    public function retrieveCategories()
    {
        $categoryCollection = Mage::getModel('catalog/category')->getCollection();
        $this->params['scope']->restrictCategoryCollection($categoryCollection);

        $categoryCollection->addAttributeToSelect('*');
        $categoryCollection->addAttributeToFilter('updated_at', array('gt' => $this->params['lastUpdate']));

        if ($this->params['categoryId']) {
            $categoryCollection->addFieldToFilter('entity_id', array('in' => $this->params['categoryId']));
        }

        if ($this->params['limit']) {
            $categoryCollection->getSelect()->limit($this->params['limit'], $this->params['start']);
        }

        if ($this->params['justCount']) {
            return $categoryCollection->count();
        }

        return $categoryCollection;
    }
}
