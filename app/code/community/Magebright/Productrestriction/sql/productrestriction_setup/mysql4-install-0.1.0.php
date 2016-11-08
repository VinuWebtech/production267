<?php
 	 
    $installer = $this;
    $installer->startSetup();
    $installer->run("
   DROP TABLE IF EXISTS {$this->getTable('productrestriction')};
    CREATE TABLE {$this->getTable('productrestriction')} (
     `productrestriction_id` int(11) NOT NULL AUTO_INCREMENT,
     `pin_code` VARCHAR( 100 ) NOT NULL ,
     `city` VARCHAR( 255 ) NOT NULL ,
     `delivery_days` VARCHAR( 50 ) NOT NULL ,
      PRIMARY KEY (`productrestriction_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
   "); 
    //$installer->installEntities(); 
 	$installer->endSetup();




