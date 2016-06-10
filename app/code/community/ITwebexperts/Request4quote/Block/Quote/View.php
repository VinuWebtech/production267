<?php
class ITwebexperts_Request4quote_Block_Quote_View extends Mage_Sales_Block_Order_View {
	
	protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('request4quote/quote/view.phtml');
    }

    protected function _prepareLayout()
    {
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle($this->__('Quote # %s', $this->getQuote()->getId()));
        }
    }
	
	public function getQuote()
	{
		return Mage::registry('current_quote');
	}
	
	public function getBackUrl()
    {
        return Mage::getUrl('*/*/history');
    }
	
	public function getBackTitle()
    {
        return Mage::helper('sales')->__('Back to My Quotes');
    }
}