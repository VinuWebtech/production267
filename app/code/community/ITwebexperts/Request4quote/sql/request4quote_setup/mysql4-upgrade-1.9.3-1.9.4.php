<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('request4quote/quote_status'), 'allowviewcheckout',array(
    'type'  =>  Varien_Db_Ddl_Table::TYPE_INTEGER,
    'comment'   =>  'Customer can view quote pricing and checkout'));

$installer->endSetup();
