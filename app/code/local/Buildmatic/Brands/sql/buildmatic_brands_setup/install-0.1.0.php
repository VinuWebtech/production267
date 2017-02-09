<?php
$installer = $this; //getting installer class object into variable
$installer->startSetup();

$installer->run("
	-- DROP TABLE IF EXIST {$this->getTable('buildmatic_brands/brands')};
	CREATE TABLE {$this->getTable('buildmatic_brands/brands')} 
	(
		`id` int(11) unsigned NOT NULL auto_increment,
		`brand_name` varchar(100) NOT NULL DEFAULT '',
		`meta_title` varchar(100) NOT NULL DEFAULT '',
		`meta_description` varchar(255) NOT NULL DEFAULT '',
		`meta_keyword` varchar(255) NOT NULL DEFAULT '',
		`description` varchar(4000) NOT NULL DEFAULT '',
		`url_key` varchar(255) NOT NULL DEFAULT '',
		`brand_image` VARCHAR(255),
		`updated_at` timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
		PRIMARY KEY  (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();