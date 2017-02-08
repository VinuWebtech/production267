<?php
class Buildmatic_Brands_Block_Brands extends Mage_Core_Block_Template
{
	public function getBrand()
	{

	}

	public function _prepareLayout() 
    {     
    	$brands = Mage::getModel('buildmatic_brands/brands')->load($this->getRequest()->getParam('id'));    
        $head = $this->getLayout()->getBlock('head');
        $head->setTitle($brands->getMetaTitle());
        $head->setKeywords($brands->getMetaKeyword());
        $head->setDescription($brands->getMetaDescription());
        $head->addLinkRel('canonical', Mage::getBaseUrl().$brands->getUrlKey());
        return parent::_prepareLayout();
	}

	public function getPriceHtml($product)
    {
        $this->setTemplate('brands/price.phtml');
        $this->setProduct($product);
        return $this->toHtml();
    }

}