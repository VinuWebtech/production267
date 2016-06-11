<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('request4quote/quote')} ADD `r4q_token` VARCHAR(255) NULL;

");

$installer->endSetup();
