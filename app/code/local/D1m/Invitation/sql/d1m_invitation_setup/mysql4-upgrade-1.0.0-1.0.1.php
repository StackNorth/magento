<?php

$installer = $this;

$installer->getConnection()
    ->addColumn(
        $installer->getTable('salesrule/coupon'),
        'customer_id',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_BIGINT,
            'comment'  => 'customer id',
            'nullable' => true
        )
    );