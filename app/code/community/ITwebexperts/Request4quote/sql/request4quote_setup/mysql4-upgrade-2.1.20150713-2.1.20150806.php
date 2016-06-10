<?php

/* @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = $this;

$setup = Mage::getResourceModel('catalog/setup', 'catalog_setup');

$setup->updateAttribute('catalog_product', 'r4q_showprice_group', array(
    'used_in_product_listing'  => true
));

$installer->endSetup();