<?php
class ITwebexperts_Request4quote_Block_Quote_Info_Abstract extends Mage_Core_Block_Template{
	
	public function getQuote()
	{
		if($this->getQuoteId()){
			return Mage::getModel('request4quote/quote')->loadByIdWithoutStore($this->getQuoteId());
		} else if(Mage::registry('current_quote')) {
			return Mage::registry('current_quote');
		}else{
			return Mage::getModel('request4quote/quote')->loadByIdWithoutStore($this->getOrder()->getId());
		}
		 //else {
		//	return $this->getOrder();
		//}
	}
	
}
