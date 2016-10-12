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
        $return = array();
        /** @var  $appEmulation Mage_Core_Model_App_Emulation */
        $appEmulation = Mage::getSingleton('core/app_emulation');
        foreach ($storeIds AS $storeId) {
            $initialScope = $this->params['scope'];
            $scope = SPM_ShopyMind_Model_Scope::fromShopymindId('store-'.$storeId);
            //echo $scope->getLang();
            $this->params['scope'] = $scope;

            $emulatedEnvironment = $appEmulation->startEnvironmentEmulation($storeId);

            $categories = $this->retrieveCategories($storeId);

            if ($this->params['justCount']) {
                return $categories->count();
            }

            $Formatter = new SPM_ShopyMind_DataMapper_Category($this->params['scope']);
            //$categories = array_values(array_map(array($Formatter, 'format'), iterator_to_array($categories->getIterator())));

            $currentReturn = array_values(array_map(array($Formatter, 'format'), iterator_to_array($categories->getIterator())));
            foreach ($currentReturn as $category) {
                $category_key = $category['id_category'].'-'.$category['lang'];
                if(!isset($return[$category_key])) $return[$category_key] = $category;
            }

            $appEmulation->stopEnvironmentEmulation($emulatedEnvironment);
            $this->params['scope'] = $initialScope;
        }

        return $return;
    }

    public function retrieveCategories($storeId = false)
    {
        $helper = Mage::helper('shopymind');
        $emulatedEnvironment = $helper->startEmulatingScope($this->params['scope']);

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

        if($storeId) {
            foreach ($categoryCollection as $key=>$category) {
                if(!in_array($storeId,$category->getStoreIds())){
                    $categoryCollection->removeItemByKey($key);
                }
            }
        }
        $helper->stopEmulation($emulatedEnvironment);
        return $categoryCollection;
    }
}
