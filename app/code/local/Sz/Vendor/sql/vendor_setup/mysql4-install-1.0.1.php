<?php
$installer = $this;
$installer->startSetup();
$installer->run("

CREATE TABLE IF NOT EXISTS {$this->getTable('vendor_product')} (
  `index_id` int(11) unsigned NOT NULL auto_increment,
  `mageproductid` int(11) NOT NULL default '0',
  `userid` int(11) NOT NULL default '0',
  `wstoreids` int(11) NOT NULL default '0',
  `status` int(11) NOT NULL default '0',
  PRIMARY KEY (`index_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$this->getTable('vendor_datafeedback')} (
  `feedid` int(11) unsigned NOT NULL auto_increment,
  `userid` smallint(6) NOT NULL default '0',
  `useremail` varchar(255) NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `proownerid` smallint(6) NOT NULL default '0',
  `feedprice` smallint(6) NOT NULL default '0',
  `feedvalue` smallint(6) NOT NULL default '0',
  `feedquality` smallint(6) NOT NULL default '0',
  `feednickname` varchar(255) NOT NULL default '',
  `feedsummary` text NOT NULL default '',
  `feedreview` text NOT NULL default '',
  `createdat` datetime NULL,
  PRIMARY KEY (`feedid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$this->getTable('vendor_feedbackcount')} (
  `feedcountid` int(11) unsigned NOT NULL auto_increment,
  `buyerid` smallint(6) NOT NULL default '0',
  `vendorid` smallint(6) NOT NULL default '0',
  `ordercount` int(11) NOT NULL default '0',
  `feedbackcount` int(11) NOT NULL default '0',
  PRIMARY KEY (`feedcountid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS {$this->getTable('vendor_saleperpartner')} (
  `autoid` int(11) unsigned NOT NULL auto_increment,
  `mageuserid` int(11) NOT NULL default '0',
  `totalsale` decimal(12,4) NOT NULL default '0',
  `amountrecived` decimal(12,4) NOT NULL default '0',
  `amountpaid` decimal(12,4) NOT NULL default '0',
  `amountremain` decimal(12,4) NOT NULL default '0',
  `commision` decimal(10,2) NOT NULL,
  PRIMARY KEY (`autoid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$this->getTable('vendor_saleslist')} (
  `autoid` int(11) NOT NULL AUTO_INCREMENT,
  `mageproid` varchar(255) NOT NULL,
  `mageorderid` varchar(255) NOT NULL,
  `magerealorderid` varchar(255) NOT NULL,
  `magequantity` varchar(255) NOT NULL,
  `mageproownerid` varchar(255) NOT NULL,
  `cpprostatus` int(2) NOT NULL,
  `magebuyerid` varchar(255) NOT NULL,
  `mageproprice` decimal(12,4) NOT NULL,
  `mageproname` varchar(255) NOT NULL,
  `totalamount` decimal(12,4) NOT NULL,
  `totalcommision` decimal(12,4) NOT NULL,
  `actualparterprocost` decimal(12,4) NOT NULL,
  `cleared_at` datetime NOT NULL,
  PRIMARY KEY (`autoid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$this->getTable('vendor_userdata')} (
  `autoid` smallint(6) NOT NULL AUTO_INCREMENT,
  `wantpartner` smallint(6) NOT NULL,
  `paymentsource` varchar(255) NOT NULL default '',
  `partnerstatus` varchar(255) NOT NULL DEFAULT 'Deafult User',
  `mageuserid` int(11) NOT NULL,
  `twitterid` varchar(255) NOT NULL,
  `facebookid` varchar(255) NOT NULL,
  `bannerpic` text NOT NULL,
  `profileurl` varchar(255) NOT NULL,
  `shoptitle` varchar(255) NOT NULL,
  `logopic` varchar(255) NOT NULL,
  `complocality` varchar(255) NOT NULL,
  `countrypic` varchar(255) NOT NULL,
  `compdesi` text NOT NULL, 
  `meta_keyword` text NOT NULL, 
  `meta_description` text NOT NULL,
  `backgroundth` varchar(255) NOT NULL,
  PRIMARY KEY (`autoid`)
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
          `contactnumber` varchar(50) NOT NULL,
          `returnpolicy` text NOT NULL,
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
          `paidstatus` int(2) NOT NULL,
          `transid` int(11) NOT NULL default '0',
          `totaltax` decimal(12,4) NOT NULL
        );
    ");
}

$installer->endSetup(); 
