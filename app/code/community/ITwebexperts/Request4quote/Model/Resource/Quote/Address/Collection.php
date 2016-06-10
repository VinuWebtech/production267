<?php
class ITwebexperts_Request4quote_Model_Resource_Quote_Address_Collection extends Mage_Sales_Model_Resource_Quote_Address_Collection {
	
	protected $_eventPrefix    = 'r4q_sales_quote_address_collection';
	protected $_eventObject    = 'r4q_quote_address_collection';
	
	protected function _construct()
    {
        $this->_init('request4quote/quote_address');
    }
}