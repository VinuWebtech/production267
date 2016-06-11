<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$table = $installer->getConnection()->addColumn($installer->getTable('request4quote/quote'), 'salesrep',array(
    'type'  =>  Varien_Db_Ddl_Table::TYPE_INTEGER,
    'comment'   =>  'Sales Representative Id (admin)'));

$installer->endSetup();
