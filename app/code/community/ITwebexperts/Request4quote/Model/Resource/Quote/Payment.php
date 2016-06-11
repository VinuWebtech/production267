<?php
class ITwebexperts_Request4quote_Model_Resource_Quote_Payment extends Mage_Sales_Model_Resource_Quote_Payment {
	
	protected function _construct()
    {
        $this->_init('request4quote/quote_payment', 'payment_id');
    }
	
}
