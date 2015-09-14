<?php
class Sz_Vendor_Block_Adminhtml_Products extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {	
	$this->_controller = 'adminhtml_products';
        $this->_headerText = Mage::helper('vendor')->__("Manage Vendor's Product");
        $this->_blockGroup = 'vendor';
        parent::__construct();
        $this->_removeButton('add');
        $this->_addButton('suo', array(
                'label'     => 'Show Unapproved Only',
                'onclick'   => 'setLocation(\'' . $this->getShowUnapprovedOnlyUrl() .'\')',
                'class'     => '',
        ));
  }
  
  public function getShowUnapprovedOnlyUrl(){
		return $this->getUrl('*/*/index/unapp/1');
	}
}
