<?php
class ITwebexperts_Request4quote_Block_Button extends Mage_Catalog_Block_Product_Abstract {
	
	public function isRequestEnabled()
	{
		return $this->getProduct()->getR4qEnabled();
	}
	
}