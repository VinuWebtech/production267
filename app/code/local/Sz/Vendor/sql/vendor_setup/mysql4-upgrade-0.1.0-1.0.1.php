<?php
$installer = $this;
$installer->startSetup();
$installer->run("
CREATE TABLE IF NOT EXISTS {$this->getTable('vendor_feedbackcount')} (
  `feedcountid` int(11) unsigned NOT NULL auto_increment,
  `buyerid` smallint(6) NOT NULL default '0',
  `vendorid` smallint(6) NOT NULL default '0',
  `ordercount` int(11) NOT NULL default '0',
  `feedbackcount` int(11) NOT NULL default '0',
  PRIMARY KEY (`feedcountid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$this->getTable('vendor_vendortransaction')} (
  `transid` int(11) unsigned NOT NULL auto_increment,
  `transactionid` varchar(255) NOT NULL default '0',
  `onlinetrid` varchar(255) NOT NULL default '0',
  `transactionamount` decimal(12,4) NOT NULL,
  `type` varchar(255) NOT NULL,
  `method` varchar(255) NOT NULL,
  `vendorid` int(11) NOT NULL default '0',
  `customnote` text NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`transid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$connection = $installer->getConnection();

if(!$connection->tableColumnExists($this->getTable('vendor_userdata'),'contactnumber')){
    $installer->run("
        ALTER TABLE {$this->getTable('vendor_userdata')} ADD COLUMN(
          `contactnumber` varchar(50) NOT NULL
        );
    ");
}
if(!$connection->tableColumnExists($this->getTable('vendor_userdata'),'returnpolicy')){
    $installer->run("
        ALTER TABLE {$this->getTable('vendor_userdata')} ADD COLUMN(
          `returnpolicy` text NOT NULL
        );
    ");
}
if(!$connection->tableColumnExists($this->getTable('vendor_userdata'),'contactnumber')){
    $installer->run("
        ALTER TABLE {$this->getTable('vendor_userdata')} ADD COLUMN(
          `shippingpolicy` text NOT NULL
        );
    ");
}

if(!$connection->tableColumnExists($this->getTable('vendor_product'),'adminassign')){
    $installer->run("
        ALTER TABLE {$this->getTable('vendor_product')} ADD COLUMN(
          `adminassign` int(2) NOT NULL default '0'
        );
    ");
}

if(!$connection->tableColumnExists($this->getTable('vendor_userdata'),'paidstatus')){
    $installer->run("
        ALTER TABLE {$this->getTable('vendor_userdata')} ADD COLUMN(
          `paidstatus` int(2) NOT NULL
        );
    ");
}
if(!$connection->tableColumnExists($this->getTable('vendor_userdata'),'transid')){
    $installer->run("
        ALTER TABLE {$this->getTable('vendor_userdata')} ADD COLUMN(
          `transid` int(11) NOT NULL default '0'
        );
    ");
}
if(!$connection->tableColumnExists($this->getTable('vendor_userdata'),'totaltax')){
    $installer->run("
        ALTER TABLE {$this->getTable('vendor_userdata')} ADD COLUMN(
          `totaltax` decimal(12,4) NOT NULL
        );
    ");
}
$installer->endSetup(); 
