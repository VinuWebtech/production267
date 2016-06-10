<?php
class ITwebexperts_Request4quote_Block_Adminhtml_Quote_Edit_Items extends Mage_Adminhtml_Block_Sales_Items_Abstract {
	
	public function getQuote()
	{
		return Mage::registry('current_quote');
	}
	
	
	protected function _beforeToHtml()
    {
        $this->setOrder($this->getQuote());
        parent::_beforeToHtml();
    }
	
	public function getItemsCollection()
    {
        return $this->getQuote()->getItemsCollection();
    }
	
	
	
	public function canEditQty()
    {
        return false;
    }

    public function canCapture()
    {
        return false;
    }
	
	
	public function canShipPartiallyItem($order = null)
    {
        return false;
    }
	
	
	public function getOrder()
    {
        return $this->getQuote();
    }
	
}