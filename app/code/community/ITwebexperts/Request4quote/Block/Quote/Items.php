<?php
class ITwebexperts_Request4quote_Block_Quote_Items extends Mage_Sales_Block_Items_Abstract {
	
	protected function _construct()
    {
        parent::_construct();
        $this->addItemRender('default', 'checkout/cart_item_renderer', 'request4quote/cart/item/default.phtml');
    }

	
	public function getQuote()
    {
        return Mage::registry('current_quote');
    }
	
	public function getOrder()
	{
		return $this->getQuote();
	}
	
}