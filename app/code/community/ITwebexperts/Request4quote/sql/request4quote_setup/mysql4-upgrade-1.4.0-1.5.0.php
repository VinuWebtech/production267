<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('request4quote/quote')} MODIFY `r4q_status` ENUM( 'new', 'processing', 'processed', 'ordered', 'declined', 'accepted', 'rejected' ) NOT NULL DEFAULT 'new';

");

$installer->endSetup();
