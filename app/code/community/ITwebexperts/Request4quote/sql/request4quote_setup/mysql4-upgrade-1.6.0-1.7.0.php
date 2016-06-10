<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("

ALTER TABLE `{$this->getTable('request4quote/quote')}` MODIFY `r4q_status` VARCHAR(255) NOT NULL DEFAULT 'new';
CREATE TABLE IF NOT EXISTS `{$this->getTable('request4quote/quote_status')}` (
  `status` varchar(32) NOT NULL COMMENT 'Status',
  `label` varchar(128) NOT NULL COMMENT 'Label',
  `is_system` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Quote Requests Status Table';


INSERT INTO `{$this->getTable('request4quote/quote_status')}` (`status`, `label`, `is_system`) VALUES
('new', 'New', 1),
('processing', 'Processing', 1),
('processed', 'Processed', 1),
('ordered', 'Ordered', 1),
('declined', 'Declined', 1),
('accepted', 'Accepted', 1),
('rejected', 'Rejected', 1);

CREATE TABLE IF NOT EXISTS `{$this->getTable('request4quote/quote_status_label')}` (
  `status` varchar(32) NOT NULL COMMENT 'Status',
  `store_id` smallint(5) unsigned NOT NULL COMMENT 'Store Id',
  `label` varchar(128) NOT NULL COMMENT 'Label',
  PRIMARY KEY (`status`,`store_id`),
  KEY `IDX_REQUEST4QUOTE_QUOTE_STATUS_LABEL_STORE_ID` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Quote Requests Status Label Table';

ALTER TABLE `{$this->getTable('request4quote/quote_status_label')}`
  ADD CONSTRAINT `FK_REQUEST4QUOTE_QUOTE_STATUS_LABEL_STATUS` FOREIGN KEY (`status`) REFERENCES `{$this->getTable('request4quote/quote_status')}` (`status`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_REQUEST4QUOTE_QUOTE_STATUS_LABEL_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE;


");

$installer->endSetup();
