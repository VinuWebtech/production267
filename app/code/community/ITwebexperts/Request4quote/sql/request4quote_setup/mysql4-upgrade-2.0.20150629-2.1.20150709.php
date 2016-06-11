<?php

/* @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->addAttribute('catalog_product', 'r4q_showprice_group', array(
    'label'                    => 'Show price to these customer groups',
    'group'                    => 'Request4Quote',
    'input'                    => 'multiselect',
    'required'                 => false,
    'source'                   => 'request4quote/source_group',
    'backend'                  => 'request4quote/backend_group',
    'apply_to'                  => 'simple,grouped,configurable,virtual,bundle,downloadable,reservation,membershippackage',
    'global'                   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'used_in_product_listing'  => false,
    'note'          => 'If customer group is enabled to see the price, this will override the hide price setting above',
));

$installer->endSetup();