<?php
class ITwebexperts_Request4quote_Block_Adminhtml_Quote extends Mage_Adminhtml_Block_Widget_Grid_Container {
	
	
	public function __construct()
    {
		$this->_blockGroup = 'request4quote';
        $this->_controller = 'adminhtml_quote';
        $this->_headerText = Mage::helper('request4quote')->__('Quote Requests');
        parent::__construct();
        //$this->_removeButton('add');
    }
	
	public function getAddButtonLabel()
	{
		return Mage::helper('request4quote')->__('Create New Quote');
	}
	
	public function getCreateUrl()
	{
		return $this->getUrl('adminhtml/quote_create/start');
	}
	
}