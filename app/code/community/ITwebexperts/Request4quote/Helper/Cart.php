<?php
class ITwebexperts_Request4quote_Helper_Cart extends Mage_Checkout_Helper_Cart {
	
	public function getCart()
    {
        return Mage::getSingleton('request4quote/cart');
    }
	
	
	public function getAddUrl($product, $additional = array())
    {
        $continueUrl    = Mage::helper('core')->urlEncode($this->getCurrentUrl());
        $urlParamName   = Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED;

        $routeParams = array(
            $urlParamName   => $continueUrl,
            'product'       => $product->getEntityId()
        );

        if (!empty($additional)) {
            $routeParams = array_merge($routeParams, $additional);
        }

        if ($product->hasUrlDataObject()) {
            $routeParams['_store'] = $product->getUrlDataObject()->getStoreId();
            $routeParams['_store_to_url'] = true;
        }

        if ($this->_getRequest()->getRouteName() == 'checkout'
            && $this->_getRequest()->getControllerName() == 'cart') {
            $routeParams['in_cart'] = 1;
        }

        return $this->_getUrl('request4quote_front/quote/add', $routeParams);
    }
	
	public function getRemoveUrl($item)
    {
        $params = array(
            'id'=>$item->getId(),
            Mage_Core_Controller_Front_Action::PARAM_NAME_BASE64_URL => $this->getCurrentBase64Url()
        );
        return $this->_getUrl('request4quote_front/quote/delete', $params);
    }
	
	public function getCartUrl()
    {
        return $this->_getUrl('request4quote_front/quote');
    }
	
	public function getQuote()
    {
        return Mage::getSingleton('request4quote/session')->getQuote();
    }
	
}
