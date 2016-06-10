<?php
class ITwebexperts_Request4quote_Block_Adminhtml_Quote_Edit_Tab_View extends Mage_Adminhtml_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface {
    
   protected $_customer;
   protected $_customrGroup;
   protected $_statusCollection;
   
   public function getTabLabel()
   {
	  return Mage::helper('customer')->__('Quote Information');
   }
   
   public function getTabTitle()
   {
	  return Mage::helper('customer')->__('Quote Information');
   }
   
   public function canShowTab()
   {
	  if (Mage::registry('current_quote')->getId()) {
		 return true;
	  }
	  return false;
   }
   
   public function isHidden()
   {
	  if (Mage::registry('current_quote')->getId()) {
		 return false;
	  }
	  return true;
   }
   
   
   public function getQuote()
   {
	 return Mage::registry('current_quote');
   }
   
   public function getShippingAddress()
   {
	   return $this->getQuote()->getShippingAddress();
   }
   
   public function getCustomer()
   {
	   if (is_null($this->_customer)) {
		   if ($this->getQuote()->getCustomerId()) {
			   $customer = Mage::getModel('customer/customer')->load($this->getQuote()->getCustomerId());
			   if ($customer->getId()) {
				   $this->_customer = $customer;
			   }
		   }
	   }
	   return $this->_customer;
   }
   
   public function getCustomerCroup()
   {
	   if (is_null($this->_customrGroup) && $this->getCustomer()) {
		   $this->_customrGroup = Mage::getModel('customer/group')->load($this->getCustomer()->getGroupId());
	   }
	   return $this->_customrGroup;
   }
   
   public function getStatusCollection()
   {
	  if (empty($this->_statusCollection)) {
		 $this->_statusCollection = Mage::getModel('request4quote/quote_status')->getCollection();
	  }
	  return $this->_statusCollection;
   }
 }