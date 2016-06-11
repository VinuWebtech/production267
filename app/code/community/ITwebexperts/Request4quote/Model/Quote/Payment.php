<?php

class ITwebexperts_Request4quote_Model_Quote_Payment extends Mage_Sales_Model_Quote_Payment {
	
	protected $_eventPrefix = 'r4q_sales_quote_payment';
    protected $_eventObject = 'r4q_payment';
	
	protected function _construct()
    {
        $this->_init('request4quote/quote_payment');
    }
	
}
