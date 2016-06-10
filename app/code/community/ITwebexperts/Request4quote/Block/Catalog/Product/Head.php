<?php
class ITwebexperts_Request4quote_Block_Catalog_Product_Head extends Mage_Core_Block_Template {
	
	public function getProduct()
    {
        if (!Mage::registry('product') && $this->getProductId()) {
            $product = Mage::getModel('catalog/product')->load($this->getProductId());
            Mage::register('product', $product);
        }
        return Mage::registry('product');
    }
	
	public function isPriceHidden()
	{
		return Mage::helper('request4quote')->isPriceHidden($this->getProduct());
	}
	
}