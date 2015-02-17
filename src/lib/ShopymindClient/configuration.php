<?php
if (! in_array('Mage', get_declared_classes())) {
    require_once dirname(__FILE__).'/../../app/Mage.php';
    umask(0);
    Mage::app();
}
return array (
  'api' =>
  array (
    'identifiant' => Mage::getStoreConfig('shopymind/configuration/apiidentification'),
    'password' => Mage::getStoreConfig('shopymind/configuration/apipassword'),
  ),
);
