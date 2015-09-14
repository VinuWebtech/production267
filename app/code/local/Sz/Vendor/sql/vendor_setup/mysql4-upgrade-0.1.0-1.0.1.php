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

ALTER TABLE {$this->getTable('vendor_userdata')} ADD COLUMN(
  `contactnumber` varchar(50) NOT NULL,
  `returnpolicy` text NOT NULL,
  `shippingpolicy` text NOT NULL
);

ALTER TABLE {$this->getTable('vendor_product')} ADD COLUMN(
  `adminassign` int(2) NOT NULL default '0'
);

ALTER TABLE {$this->getTable('vendor_saleslist')} ADD COLUMN(
  `paidstatus` int(2) NOT NULL,
  `transid` int(11) NOT NULL default '0',
  `totaltax` decimal(12,4) NOT NULL
);
");

$installer->endSetup(); 
