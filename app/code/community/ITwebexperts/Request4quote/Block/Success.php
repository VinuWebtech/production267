<?php
class ITwebexperts_Request4quote_Block_Success extends Mage_Core_Block_Template {
	
	protected function _isLoggedIn()
	{
		return Mage::getSingleton('customer/session')->isLoggedIn();
	}
	
	protected function _getSession()
    {
        return Mage::getSingleton('request4quote/session');
    }
	
	public function getLastQuoteId()
	{
		return $this->_getSession()->getLastSuccessQuoteId();
	}
	
	public function getLastQuoteCode()
	{
		return substr(sha1($this->getLastQuoteId()), 0, 6);
	}
}