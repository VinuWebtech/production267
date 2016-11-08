<?php

    $installer = $this;
    $installer->startSetup();
    $installer->run("
    	DROP TABLE IF EXISTS {$this->getTable('zipcodeproduct')};
    	CREATE TABLE {$this->getTable('zipcodeproduct')} (
     		`zipcodeproduct_id` int(11) NOT NULL AUTO_INCREMENT,
     		`pin_code` VARCHAR( 100 ) NOT NULL ,
     		`sku` VARCHAR( 200 ) NOT NULL default 0,
    		`product_id` int(11) NOT NULL default 0, 
      		PRIMARY KEY (`zipcodeproduct_id`)
    	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
   	");
 	$installer->endSetup();