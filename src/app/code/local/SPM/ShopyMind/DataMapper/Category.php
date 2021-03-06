<?php

class SPM_ShopyMind_DataMapper_Category
{
    private $mapping = array(
        'id_category' => 'entity_id',
        'id_parent_category' => 'parent_id',
        'name' => 'name',
        'description' => 'description',
        'date_creation' => 'created_at',
        'active' => 'is_active',
    );

    private $category;
    private $scope;
    private $transformations;

    public function __construct(SPM_ShopyMind_Model_Scope $scope)
    {
        $this->scope = $scope;;
    }

    public function format(Mage_Catalog_Model_Category $category) {
        $this->category = $category;
        $this->transformations = array(
            'link' => array($this->category, 'getUrl'),
            'shop_id_shop' => array($this->scope, 'getId'),
            'lang' => array($this, 'getFormattedLocale'),
            'date_creation' => array($this, 'formatDateTime')
        );
        $formattedData = new Varien_Object();
        $categoryData = $this->category->getData();

        foreach($this->mapping as $shopymindKey => $magentoKey) {
            $formattedData->setData($shopymindKey, $categoryData[$magentoKey]);
        }
        $formattedData = $this->transformComplexMappedData($formattedData);

        return $formattedData->getData();
    }

    protected function getFormattedLocale()
    {
        $lang = $this->scope->getConfig('general/locale/code');
        if (empty($lang)) {
            return $lang;
        }

        return substr($lang, 0, -3);
    }

    protected function getStoreId()
    {
        return Mage::app()->getStore()->getId();
    }

    protected function formatDateTime(Varien_Object $formattedData)
    {
        return date('Y-m-d H:i:s', strtotime($formattedData->getData('date_creation')));
    }

    private function transformComplexMappedData(Varien_Object $formattedData)
    {
        foreach ($this->transformations as $key => $callable) {
            $formattedData->setData($key, call_user_func($callable, $formattedData));
        }

        return $formattedData;
    }
}
