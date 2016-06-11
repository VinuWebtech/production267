<?php
class ITwebexperts_Request4quote_Model_Adminhtml_Session_Quote extends Mage_Adminhtml_Model_Session_Quote {
	
	public function __construct()
    {
        $this->init('request4quote_adminhtml_quote');
        if (Mage::app()->isSingleStoreMode()) {
            $this->setStoreId(Mage::app()->getStore(true)->getId());
        }
    }
	
	
	public function getQuote()
    {
        if (is_null($this->_quote)) {
            $this->_quote = Mage::getModel('request4quote/quote');
            if ($this->getStoreId() && $this->getQuoteId()) {
                $this->_quote->setStoreId($this->getStoreId())
                    ->load($this->getQuoteId());
            }
            elseif($this->getStoreId() && $this->hasCustomerId()) {
                $this->_quote->setStoreId($this->getStoreId())
                    ->setCustomerGroupId(Mage::getStoreConfig(self::XML_PATH_DEFAULT_CREATEACCOUNT_GROUP))
                    ->assignCustomer($this->getCustomer())
                    ->setIsActive(false)
                    ->save();
                $this->setQuoteId($this->_quote->getId());
            }
            $this->_quote->setIgnoreOldQty(true);
            $this->_quote->setIsSuperMode(true);
        }
        return $this->_quote;
    }
	
	public function getOrder()
	{
		return new Varien_Object();
	}
	
}
