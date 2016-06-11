<?php
class ITwebexperts_Request4quote_Block_Adminhtml_Quote_Status_New extends Mage_Adminhtml_Block_Widget_Form_Container {
	
	public function __construct()
    {
        $this->_objectId = 'status';
		$this->_blockGroup = 'request4quote';
        $this->_controller = 'adminhtml_quote_status';
        $this->_mode = 'new';

        parent::__construct();
        $this->_updateButton('save', 'label', Mage::helper('request4quote')->__('Save Status'));
        $this->_removeButton('delete');
    }
	
	public function getHeaderText()
    {
        return Mage::helper('sales')->__('New Quote Status');
    }
}