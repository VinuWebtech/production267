<?php
class ITwebexperts_Request4quote_Block_Catalog_Product_Viewprice extends ITwebexperts_Payperrentals_Block_Catalog_Product_Viewprice {
	
	public function getPriceList(){
		if ($this->getProduct()->getCanShowPrice()) {
            return ITwebexperts_Payperrentals_Helper_Price::getPriceListHtml($this->getProduct(), Mage::getStoreConfig(ITwebexperts_Payperrentals_Helper_Data::XML_PATH_PRICING_ON_LISTING));
		} else {
			return '';
		}
		
	}
	
}
