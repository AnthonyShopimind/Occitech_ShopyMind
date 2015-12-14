<?php

class SPM_ShopyMind_Action_GetCategory implements SPM_ShopyMind_Interface_Action
{
    private $scope;
    private $categoryId;
    private $Category;

    public function __construct(SPM_ShopyMind_Model_Scope $scope, $idCategory)
    {
        $this->Category = Mage::getModel('catalog/category');
        $this->categoryId = $idCategory;
        $this->scope = $scope;
    }

    public function process()
    {
        $collection = $this->Category->getCollection();
        $collection->addAttributeToFilter('entity_id', array('eq' => $this->categoryId))
            ->addAttributeToSelect('*');

        $this->scope->restrictCategoryCollection($collection);
        $category = $collection->getFirstItem();

        if (is_null($this->categoryId) || !$category->getId()) {
            return array();
        }

        return $category;
    }
}
