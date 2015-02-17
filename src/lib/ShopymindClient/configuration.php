<?php
if (! in_array('Mage', get_declared_classes())) {
    require_once dirname(__FILE__).'/../../app/Mage.php';
    umask(0);
    Mage::app();
}

$scope = SPM_ShopyMind_Model_Scope::fromRequest();

return array (
  'api' =>
  array (
    'identifiant' => (string) $scope->getConfig('shopymind/configuration/apiidentification'),
    'password' => (string) $scope->getConfig('shopymind/configuration/apipassword'),
  ),
);
