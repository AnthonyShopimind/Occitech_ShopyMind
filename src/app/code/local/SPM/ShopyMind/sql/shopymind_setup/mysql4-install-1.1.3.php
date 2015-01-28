<?php
$installer = $this;
$installer->startSetup();
$installer->run ( "CREATE TABLE IF NOT EXISTS {$this->getTable('spmcartoorder')} (`id_cart` int(10) unsigned NOT NULL,`id_customer` int(10) unsigned NOT NULL DEFAULT '0',`id_order` int(10) unsigned NOT NULL DEFAULT '0',`spm_key` varchar(40) COLLATE utf8_bin NOT NULL DEFAULT '',`is_converted` tinyint(1) unsigned NOT NULL DEFAULT '0',`date_add` datetime NOT NULL,`date_upd` datetime NOT NULL,PRIMARY KEY (`spm_key`),KEY `is_converted` (`is_converted`),KEY `date_upd` (`date_upd`),KEY `date_add` (`date_add`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;" );
$installer->getConnection()->addColumn(
$installer->getTable('spmcartoorder'),
        'email',
        array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'unsigned'  => true,
                'nullable'  => true,
                'comment'   => 'Customer email'
        )
);
$installer->getConnection()->addIndex($installer->getTable('spmcartoorder'), 'email', 'email');

$installer->getConnection()->addColumn(
$installer->getTable('spmcartoorder'),
'voucher_number',
array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 20,
        'unsigned'  => true,
        'nullable'  => true,
        'comment'   => 'Voucher used'
)
);
$installer->getConnection()->addIndex($installer->getTable('spmcartoorder'), 'voucher_number', 'voucher_number');
$installer->endSetup();
?>