<?php
$installer = $this;
$installer->startSetup();
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
if(!$connection->tableColumnExists($this->getTable('vendor_userdata'),'shippingpolicy')){
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
if(!$connection->tableColumnExists($this->getTable('vendor_product'),'file_id')){
    $installer->run("
        ALTER TABLE {$this->getTable('vendor_product')} ADD COLUMN(
           `file_id` int(11) NOT NULL default '0'
        );
    ");
}
if(!$connection->tableColumnExists($this->getTable('vendor_saleslist'),'paidstatus')){
    $installer->run("
        ALTER TABLE {$this->getTable('vendor_saleslist')} ADD COLUMN(
           `paidstatus` int(2) NOT NULL
        );
    ");
}
if(!$connection->tableColumnExists($this->getTable('vendor_saleslist'),'transid')){
    $installer->run("
        ALTER TABLE {$this->getTable('vendor_saleslist')} ADD COLUMN(
           `transid` int(11) NOT NULL default '0'
        );
    ");
}
if(!$connection->tableColumnExists($this->getTable('vendor_saleslist'),'totaltax')){
    $installer->run("
        ALTER TABLE {$this->getTable('vendor_saleslist')} ADD COLUMN(
           `totaltax` decimal(12,4) NOT NULL
        );
    ");
}
if(!$connection->tableColumnExists($this->getTable('vendor_product_files'),'file_type')){
    $installer->run("
        ALTER TABLE {$this->getTable('vendor_product_files')} ADD COLUMN(
           `file_type` int(2) NOT NULL default '1'
        );
    ");
}

$installer->endSetup(); 
