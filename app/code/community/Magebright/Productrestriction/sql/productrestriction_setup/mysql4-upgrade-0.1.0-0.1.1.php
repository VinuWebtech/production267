<?php
 
$installer = $this;
 
$installer->startSetup();
 
$installer->run("ALTER TABLE {$this->getTable('productrestriction')} ADD COLUMN cod TINYINT NOT NULL default 0 ");
//$installer->run("ALTER TABLE {$this->getTable('productrestriction')} ADD COLUMN product_id varchar(200) NOT NULL default '' ");
 
$installer->endSetup();

