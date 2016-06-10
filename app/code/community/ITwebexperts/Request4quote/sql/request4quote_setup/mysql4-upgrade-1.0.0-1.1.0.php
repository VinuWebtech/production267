<?php

$installer = $this;
$installer->startSetup();

// remove attributes
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->removeAttribute('catalog_product', 'r4q_enabled');
$setup->removeAttribute('catalog_product', 'r4q_order_disabled');
$setup->removeAttribute('catalog_product', 'r4q_hide_price');

// add attributes
$installer->addAttribute('catalog_product', 'r4q_enabled', array(
	'backend'       => '',
	'type' => 'int',
	'input' => 'select',
	'source'  => 'eav/entity_attribute_source_boolean',
	'group'		=> 'Request4Quote',
	'label'         => 'Allow Quotation Requests',
	'class'         => '',
	'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
	'default_value' => 0,
	'visible'       => true,
	'used_in_product_listing' => true,
	'is_visible_on_front' => false,
	'required'      => false,
	'user_defined'  => false,
	'default'       => '0',
	'apply_to'      => 'simple,grouped,configurable,virtual,bundle,downloadable,reservation,membershippackage',
	'visible_on_front' => false,
	'position'      =>  1,
));

$installer->addAttribute('catalog_product', 'r4q_order_disabled', array(
	'backend'       => '',
	'type' => 'int',
	'input' => 'select',
	'source'  => 'eav/entity_attribute_source_boolean',
	'group'		=> 'Request4Quote',
	'label'         => 'Disable Add To Cart',
	'class'         => '',
	'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
	'default_value' => 0,
	'visible'       => true,
	'used_in_product_listing' => true,
	'is_visible_on_front' => false,
	'required'      => false,
	'user_defined'  => false,
	'default'       => '0',
	'apply_to'      => 'simple,grouped,configurable,virtual,bundle,downloadable,reservation,membershippackage',
	'visible_on_front' => false,
	'position'      =>  2,
));

$installer->addAttribute('catalog_product', 'r4q_hide_price', array(
	'backend'       => '',
	'type' => 'int',
	'input' => 'select',
	'source'  => 'eav/entity_attribute_source_boolean',
	'group'		=> 'Request4Quote',
	'label'         => 'Hide Price',
	'class'         => '',
	'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
	'default_value' => 0,
	'visible'       => true,
	'used_in_product_listing' => true,
	'is_visible_on_front' => false,
	'required'      => false,
	'user_defined'  => false,
	'default'       => '0',
	'note'          => 'Warning: if the price is hidden and the product is orderable, prices will show in checkout',
	'apply_to'      => 'simple,grouped,configurable,virtual,bundle,downloadable,reservation,membershippackage',
	'visible_on_front' => false,
	'position'      =>  3,
));

$installer->endSetup();