<?php
class Magebright_Productrestriction_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
            $this->loadLayout();
            $this->renderLayout();
    }
   public function checkcodeAction() {
         $postcode = Mage::app()->getRequest()->getParam('zipcode');            
         $product_id=Mage::app()->getRequest()->getParam('productid'); 
             $response = array();                 
             $result = Mage::helper('productrestriction')->checkProductrestrictionData($postcode,array($product_id));
       		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));  
    }

    /* Vinu changes start to add functionality for allow shipping to all pincodes in system */
    public function checksinglecodeAction() {
        $postcode = Mage::app()->getRequest()->getParam('zipcode');            
        $product_id=Mage::app()->getRequest()->getParam('productid'); 
        $response = array();                 
        $result = Mage::helper('productrestriction')->checkSingleProductrestrictionData($postcode,$product_id);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));  
    }
    /* Vinu changes end to add functionality for allow shipping to all pincodes in system */
    
}