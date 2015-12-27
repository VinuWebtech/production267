<?php
/**
 * Email Log Installer
 * 
 */

$installer = $this;

$installer->startSetup();

Mage::log("Running installer");

$installer->run("
CREATE TABLE `{$this->getTable('log_email')}` (
  `email_id` int(10) unsigned NOT NULL auto_increment,
  `log_at` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `email_to` varchar(255) NOT NULL default '',
  `template` varchar(255) NULL,
  `subject` varchar(255) NULL,
  `email_body` text,
  PRIMARY KEY  (`email_id`),
  KEY `log_at` (`log_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();
