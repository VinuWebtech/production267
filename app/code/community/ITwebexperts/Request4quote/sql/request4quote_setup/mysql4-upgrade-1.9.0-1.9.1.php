<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('request4quote/quote')} ADD `r4q_shipping_as_billing` INT(1) NOT NULL DEFAULT '0';

");

$installer->endSetup();
