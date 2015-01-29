<?php
$installer = $this;
$installer->startSetup();
$installer->run ( "CREATE TABLE IF NOT EXISTS {$this->getTable('spmcartoorder')} (`id_cart` int(10) unsigned NOT NULL,`id_customer` int(10) unsigned NOT NULL DEFAULT '0',`id_order` int(10) unsigned NOT NULL DEFAULT '0',`spm_key` varchar(40) COLLATE utf8_bin NOT NULL DEFAULT '',`is_converted` tinyint(1) unsigned NOT NULL DEFAULT '0',`date_add` datetime NOT NULL,`date_upd` datetime NOT NULL,PRIMARY KEY (`spm_key`),KEY `is_converted` (`is_converted`),KEY `date_upd` (`date_upd`),KEY `date_add` (`date_add`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;" );
$installer->getConnection()->addColumn(
        $installer->getTable('spmcartoorder'),
        'email',
        "varchar(255) NULL COMMENT 'Customer email'"
);
$installer->run(sprintf('ALTER TABLE %s ADD INDEX `email` (`email`)', $this->getTable('spmcartoorder')));

$installer->getConnection()->addColumn(
        $installer->getTable('spmcartoorder'),
        'voucher_number',
        "varchar(20) NULL COMMENT 'Voucher used'"
);
$installer->run(sprintf('ALTER TABLE %s ADD INDEX `voucher_number` (`voucher_number`)', $this->getTable('spmcartoorder')));

$installer->endSetup();
?>
