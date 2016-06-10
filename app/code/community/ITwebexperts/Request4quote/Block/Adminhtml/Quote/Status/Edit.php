<?php
class ITwebexperts_Request4quote_Block_Adminhtml_Quote_Status_Edit extends ITwebexperts_Request4quote_Block_Adminhtml_Quote_Status_New {
	
	public function __construct()
    {
        parent::__construct();
        $this->_mode = 'edit';
    }
	
	public function getHeaderText()
    {
        return Mage::helper('sales')->__('Edit Order Status');
    }
}