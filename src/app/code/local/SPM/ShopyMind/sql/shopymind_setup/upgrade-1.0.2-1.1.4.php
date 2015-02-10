<?php
$installer = $this;
$installer->startSetup();
$installer->getConnection()->addColumn(
        $installer->getTable('spmcartoorder'),
        'email',
        "varchar(255) NULL COMMENT 'Customer email'"
);
try {
        $installer->run(sprintf('ALTER TABLE %s ADD INDEX `email` (`email`)', $this->getTable('spmcartoorder')));
} catch (Exception $e) {
        Mage::log($e->getMessage()); // It is very likely to exist
}

$installer->getConnection()->addColumn(
        $installer->getTable('spmcartoorder'),
        'voucher_number',
        "varchar(20) NULL COMMENT 'Voucher used'"
);
try {
        $installer->run(sprintf('ALTER TABLE %s ADD INDEX `voucher_number` (`voucher_number`)', $this->getTable('spmcartoorder')));
} catch (Exception $e) {
        Mage::log($e->getMessage()); // It is very likely to exist
}

$installer->endSetup();
?>
