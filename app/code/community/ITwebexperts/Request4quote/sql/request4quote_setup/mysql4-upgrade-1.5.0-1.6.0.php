<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('request4quote/quote')} ADD `r4q_decline_reason` MEDIUMTEXT NULL;
ALTER TABLE {$this->getTable('request4quote/quote')} ADD `r4q_reject_reason` MEDIUMTEXT NULL;

");

$installer->endSetup();
