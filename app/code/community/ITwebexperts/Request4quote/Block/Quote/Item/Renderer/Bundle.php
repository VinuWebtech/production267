<?php

if (Mage::helper('request4quote')->isRentalInstalled()) {


    class ITwebexperts_Request4quote_Block_Quote_Item_Renderer_Bundle extends ITwebexperts_Payperrentals_Block_Sales_Order_Items_Renderer {
		public function getOrderItem()
		{
			if ($this->getItem() instanceof Mage_Sales_Model_Quote_Item) {
				return $this->getItem();
			} else {
				return $this->getItem()->getOrderItem();
			}
		}
		
		public function getOrder()
		{
			return $this->getOrderItem()->getQuote();
		}
		
		public function getQuote()
		{
			return $this->getOrderItem()->getQuote();
		}
		
		public function getItemOptions()
		{
			$helper = Mage::helper('bundle/catalog_product_configuration');
			$options = $helper->getOptions($this->getItem());
			foreach ($options AS &$option) {
				if (is_array($option['value'])) {
					if (isset($option['value'][0])) {
						$option['value'] = strip_tags($option['value'][0]);
					}
				}
			}
			$infoByRequest = $this->getOrderItem()->getOptionByCode('info_buyRequest');
			if ($infoByRequest) {
				$this->getOrderItem()->setProductOptions(array(
					'info_buyRequest' => unserialize($infoByRequest->getValue())
				));
				$options = array_merge($options, parent::getItemOptions());
			}
			return $options;
		}
		
	}
	
} else {
	
	class ITwebexperts_Request4quote_Block_Quote_Item_Renderer_Bundle extends ITwebexperts_Request4quote_Block_Quote_Item_Renderer_Default {
		public function getOrderItem()
		{
			if ($this->getItem() instanceof Mage_Sales_Model_Quote_Item) {
				return $this->getItem();
			} else {
				return $this->getItem()->getOrderItem();
			}
		}
		
		public function getOrder()
		{
			return $this->getOrderItem()->getQuote();
		}
		
		public function getQuote()
		{
			return $this->getOrderItem()->getQuote();
		}
		
		public function getItemOptions()
		{
			$helper = Mage::helper('bundle/catalog_product_configuration');
			$options = $helper->getOptions($this->getItem());
			foreach ($options AS &$option) {
				if (is_array($option['value'])) {
					if (isset($option['value'][0])) {
						$option['value'] = strip_tags($option['value'][0]);
					}
				}
			}
			return $options;
		}
		
	}
}
