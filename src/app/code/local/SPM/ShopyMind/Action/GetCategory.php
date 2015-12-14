<?php

class SPM_ShopyMind_Action_GetCategory implements SPM_ShopyMind_Interface_Action
{
    private $scope;
    private $categoryId;
    private $Category;
    private $Formatter;

    public function __construct(SPM_ShopyMind_Model_Scope $scope, $categoryId)
    {
        $this->Category = Mage::getModel('catalog/category');
        $this->Formatter = new SPM_ShopyMind_DataMapper_Category();

        $this->categoryId = $categoryId;
        $this->scope = $scope;
    }

    public function process($formatData = true)
    {
        $storeIds = $this->scope->storeIds();

        ShopymindClient_Callback::startStoreEmulationByStoreId($storeIds[0]);
        $collection = $this->Category->getCollection();
        $this->scope->restrictCategoryCollection($collection);
        $collection->addAttributeToFilter('entity_id', array('eq' => $this->categoryId))
            ->addAttributeToSelect('*');

        $category = $collection->getFirstItem();

        if (is_null($this->categoryId) || !$category->getId()) {
            return array();
        }

        if ($formatData) {
            $data = $this->Formatter->format($category);
        } else {
            $data = $category;
        }
        ShopymindClient_Callback::stopStoreEmulation();

        return $data;
    }
}
