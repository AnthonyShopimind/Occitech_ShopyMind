<?php
$installer = $this;
$installer->startSetup();
$installer->getConnection()->addColumn(
        $installer->getTable('spmcartoorder'),
        'email',
        "varchar(255) NULL COMMENT 'Customer email'"
);
$installer->getConnection()->addIndex($installer->getTable('spmcartoorder'), 'email', 'email');

$installer->getConnection()->addColumn(
        $installer->getTable('spmcartoorder'),
        'voucher_number',
        "varchar(20) NULL COMMENT 'Voucher used'"
);
$installer->getConnection()->addIndex($installer->getTable('spmcartoorder'), 'voucher_number', 'voucher_number');
$installer->endSetup();
?>
