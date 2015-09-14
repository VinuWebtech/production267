<?php
class Sz_Vendor_Block_Adminhtml_Partners extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
	$this->_controller = 'adminhtml_partners';
	$this->_headerText = Mage::helper('vendor')->__("Manage Vendors");
	$this->_blockGroup = 'vendor';
	parent::__construct();
	$this->_removeButton('add');
	$this->_removeButton('reset_filter_button');
	$this->_removeButton('search_button'); 
  }
}
