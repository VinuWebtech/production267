<?php
class ITwebexperts_Request4quote_Model_Resource_Quote_Address extends Mage_Sales_Model_Resource_Quote_Address {
	
	protected function _construct()
    {
        $this->_init('request4quote/quote_address', 'address_id');
    }
	
}