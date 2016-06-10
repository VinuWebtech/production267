<?php
class ITwebexperts_Request4quote_Block_Adminhtml_Quote_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {
	
	public function __construct()
    {
        parent::__construct();
        $this->setId('request4quote_quote_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('customer')->__('Quote Information'));
    }
}