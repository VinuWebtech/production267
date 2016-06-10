<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$table = $installer->getConnection()->newTable($installer->getTable('request4quote/comments'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'identity' => true,
        'nullable' => false,
        'primary'  => true,
    ), 'id')
    ->addColumn('r4q_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(), 'id')
    ->addColumn('comment', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(), 'comment')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(), 'status')
    ->addColumn('is_customer_notified', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(), 'customer notified')
    ->addColumn('is_visible_on_front', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(), 'visible on frontend')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'time created')
    ->addColumn('submitted_by', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(), 'Comment submitted by admin or customer');

$installer->getConnection()->createTable($table);

$installer->endSetup();
