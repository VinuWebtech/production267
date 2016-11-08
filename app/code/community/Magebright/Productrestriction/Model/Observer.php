<?php 
class Magebright_Productrestriction_Model_Observer
{
    
    public function addToCartBeforeDispatch($observer)
    {
      
        if($observer->getEvent()->getControllerAction()->getFullActionName() == 'checkout_cart_add') 
        {
        	
	         if(Mage::getStoreConfig('productrestriction/general/enabled') == 1 ){	
		         $redirect_url= Mage::getUrl('customer/account/login/');
			     $current_url = Mage::helper('core/url')->getCurrentUrl();
		    	 //if((!Mage::helper('customer')->isLoggedIn()) && ($current_url != $redirect_url)){
		    			$productId = Mage::app()->getRequest()->getParam('product');	
				    	 $zip = Mage::app()->getRequest()->getParam('zipcode_hidden');
				    	
						   $allow= Mage::helper('productrestriction')->checkProductrestrictionData($zip,array($productId));
						
							  if($allow['valid']==0){
							  	$msg = Mage::getStoreConfig('productrestriction/general/product_msg');
			       				$product = Mage::getModel('catalog/product')->load($productId);
							  	Mage::getSingleton('core/session')->addError($msg);
							  	$lastUrl = Mage::getSingleton('core/session')->getLastUrl();
							   $product->getProductUrl();
							  	
							   	header("Location: " . $product->getProductUrl());
			            		die();
							  }
				 	 
				    //}
	          }
        }
    }
    
  
	
}	
    
    