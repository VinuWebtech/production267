<?php
class Buildmatic_Brands_Block_Adminhtml_Brands extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_brands';
		$this->_blockGroup = 'buildmatic_brands';
		$this->_headerText = Mage::helper('buildmatic_brands')->__('Brands Manager');
		$this->_addButtonLabel = Mage::helper('buildmatic_brands')->__('Add Brand');
		parent::__construct();
	}
}