<?php

class Magebright_Productrestriction_Block_Product_Productrestriction extends Mage_Catalog_Block_Product_View
{
	 
	protected $_productrestrictionCollection;
	 
    public function getProductrestrictionCollection($product)
    {
    	if($product)
    	{
    		$productProductrestriction = $product->getProductrestriction();
    	  	$pos = strpos($productProductrestriction,',');
    		if ($pos === false)  
    		{
    			 
    			 $this->_productrestrictionCollection[] = Mage::getModel('productrestriction/productrestriction')->getCollection()  
				                			->addFieldToFilter('productrestriction_id', $productProductrestriction);
	   		} else {
    			
    			$arrProductProductrestriction = explode(',',$productProductrestriction);  
				foreach ($arrProductProductrestriction as $awId)
				{
					  $this->_productrestrictionCollection[] = Mage::getModel('productrestriction/productrestriction')->getCollection()  
		                ->addFieldToFilter('productrestriction_id', $awId); 
				}           
    		}
 
 
    	}
    
        return $this->_productrestrictionCollection;
    }
}