<?php
$installer = $this;
$installer->startSetup();
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