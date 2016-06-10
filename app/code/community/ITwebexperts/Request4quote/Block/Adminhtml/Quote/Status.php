<?php
class ITwebexperts_Request4quote_Block_Adminhtml_Quote_Status extends Mage_Adminhtml_Block_Widget_Grid_Container {
	
	public function __construct()
    {
		$this->_blockGroup = 'request4quote';
        $this->_controller = 'adminhtml_quote_status';
        $this->_headerText = Mage::helper('request4quote')->__('Quote Statuses');
        $this->_addButtonLabel = Mage::helper('sales')->__('Create New Status');
        parent::__construct();
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/quote_status/new');
    }

	
}