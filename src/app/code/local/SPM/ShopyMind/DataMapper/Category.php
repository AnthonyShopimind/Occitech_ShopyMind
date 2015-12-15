<?php

class SPM_ShopyMind_DataMapper_Category
{
    private $mapping = array(
        'shop_id_shop' => 'store_id',
        'id_category' => 'entity_id',
        'id_parent_category' => 'parent_id',
        'lang' => 'locale',
        'name' => 'name',
        'description' => 'description',
        'link' => 'url_key',
        'date_creation' => 'created_at',
        'active' => 'is_active',
    );

    private $category;
    private $transformations;

    public function __construct(Mage_Catalog_Model_Category $category)
    {
        $this->category = $category;
        $this->transformations = array(
            'link' => array($this->category, 'getUrl'),
            'shop_id_shop' => array($this->category, 'getStoreId'),
        );
    }


    public function format() {
        $formattedData = new Varien_Object();
        $categoryData = $this->category->getData();

        foreach($this->mapping as $shopymindKey => $magentoKey) {
            $formattedData->setData($shopymindKey, $categoryData[$magentoKey]);
        }
        $formattedData = $this->transformComplexMappedData($formattedData);

        return $formattedData->getData();
    }

    private function transformComplexMappedData(Varien_Object $formattedData)
    {
        foreach ($this->transformations as $key => $callable) {
            $formattedData->setData($key, call_user_func($callable));
        }

        return $formattedData;
    }

}
