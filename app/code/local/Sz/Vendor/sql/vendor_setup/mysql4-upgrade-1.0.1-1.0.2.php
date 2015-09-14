<?php
$installer = $this;
$installer->startSetup();
$query = "
CREATE TABLE IF NOT EXISTS {$this->getTable('vendor_product_files')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `vendor_id` smallint(6) NOT NULL default '0',
  `attribute_set` smallint(6) NOT NULL default '0',
  `file_name` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` int(11) NOT NULL default '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";
$installer->run($query);

$installer->endSetup(); 
