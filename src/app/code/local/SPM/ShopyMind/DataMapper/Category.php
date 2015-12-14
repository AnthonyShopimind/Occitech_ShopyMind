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

    public function format(Mage_Catalog_Model_Category $category) {
        $formattedData = new Varien_Object();
        $categoryData = $category->getData();

        foreach($this->mapping as $shopymindKey => $magentoKey) {
            $formattedData->setData($shopymindKey, $categoryData[$magentoKey]);
            $formattedData = $this->transform($shopymindKey, $category, $formattedData);
        }

        return $formattedData->getData();
    }

    private function transform($currentKey, Mage_Catalog_Model_Category $category, Varien_Object $formattedData)
    {
        switch ($currentKey) {
            case 'link':
                $formattedData->setData($currentKey, $category->getUrl());
                break;
            case 'shop_id_shop':
                $formattedData->setData($currentKey, $category->getStoreId());
                break;
            default:
                return $formattedData;
            break;
        }

        return $formattedData;
    }

}
