<?php
class Buildmatic_Brands_Block_Brands_Category extends Mage_Core_Block_Template
{

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

    public function getCatProduct($category,$brands)
    {
        $collection = Mage::getModel('catalog/category')
                    ->load($category)
                    ->getProductCollection();

        $attr = $collection->getResource()->getAttribute("manufacturer");
        if ($attr->usesSource()) 
        {
            $manufacturer_id = $attr->getSource()->getOptionId($brands->getBrandName());
        }
        $collection = $collection->addAttributeToFilter('manufacturer', $manufacturer_id); 
        return $collection;
    }
}