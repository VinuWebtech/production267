<?php
class Magebright_Productrestriction_Model_Checkout_Cart extends Mage_Checkout_Model_Cart
{
   
    public function addProduct($productInfo, $requestInfo=null)
    {
        $product = $this->_getProduct($productInfo);
        Mage::dispatchEvent('add_to_cart_before', array('product' => $product));
         
        return parent::addProduct($productInfo, $requestInfo=null);
    }
}