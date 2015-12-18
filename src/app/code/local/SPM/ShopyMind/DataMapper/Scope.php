<?php

class SPM_ShopyMind_DataMapper_Scope
{

    public function format(SPM_ShopyMind_Model_Scope $scope)
    {
        $shopymind = Mage::helper('shopymind');
        $emulatedEnvironment = $shopymind->startEmulatingScope($scope);
        $scopeData = array(
            'shop_id_shop' => $scope->getId(),
            'lang' => $scope->getLang(),
            'currency' => $scope->currencyCode()
        );
        $shopymind->stopEmulation($emulatedEnvironment);

        return $scopeData;

    }

    public static function makeScopeEnricher(SPM_ShopyMind_Model_Scope $scope)
    {
        $formatter = new SPM_ShopyMind_DataMapper_Scope();
        $scopeData = $formatter->format($scope);

        return function ($dataToEnrich) use ($scopeData) {
            return array_merge($dataToEnrich, $scopeData);
        };
    }
}
