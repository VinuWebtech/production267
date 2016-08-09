<?php
class ITwebexperts_Request4quote_Block_Button extends Mage_Catalog_Block_Product_Abstract {
	
	public function isRequestEnabled()
	{
		return $this->getProduct()->getR4qEnabled();
	}
	
	//vinu changes for bulk order button for product
	public function isRequestOrderDisabled()
	{
		return $this->getProduct()->getR4qOrderDisabled();
	}

	public function isDiscountOffered()
	{
		return $this->getProduct()->getDisPerOffer();
	}
	//vinu changes for bulk order button end
	
}