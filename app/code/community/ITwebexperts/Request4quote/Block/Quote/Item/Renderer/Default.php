<?php

if (Mage::helper('request4quote')->isRentalInstalled()) {
	class ITwebexperts_Request4quote_Block_Quote_Item_Renderer_Default extends ITwebexperts_Payperrentals_Block_Sales_Order_Items_Renderer {
	
	
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
		
		public function getProductOptions()
		{
			$helper = Mage::helper('catalog/product_configuration');
			$options = $helper->getCustomOptions($this->getItem());
			$newResult = Mage::helper('payperrentals/rendercart')->renderDates($options, $this->getItem());
			$options = array_merge($newResult, $options);
			/*$infoByRequest = $this->getOrderItem()->getOptionByCode('info_buyRequest');
			if ($infoByRequest) {
				$this->getOrderItem()->setProductOptions(array(
					'info_buyRequest' => unserialize($infoByRequest->getValue())
				));
				$options = array_merge($options, parent::getItemOptions());
			}*/
			return $options;

            /*$result = array();
            $newResult = Mage::helper('payperrentals/rendercart')->renderDates($options, $this->getItem());
            $result = array_merge($newResult, $result);
			if (isset($options['info_buyRequest']['options'])) {
				$result = array_merge($result, $options['info_buyRequest']['options']);
			}
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }

            return $result;*/
		}
		
		public function getItemOptions()
		{
			return $this->getProductOptions();
		}
		
	}
	
} else {
	
	class ITwebexperts_Request4quote_Block_Quote_Item_Renderer_Default extends Mage_Sales_Block_Order_Item_Renderer_Default {
	
	
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
		
		public function getProductOptions()
		{
			$helper = Mage::helper('catalog/product_configuration');
			return $helper->getCustomOptions($this->getItem());
		}
		
		public function getItemOptions()
		{
			return $this->getProductOptions();
		}

		//vinu changes added for display of option for configurable product 
		public function getCItemOptions()
		{
			$helper = Mage::helper('catalog/product_configuration');
			$options = $helper->getConfigurableOptions($this->getItem());
			return $options;
		}
		
	}
	
}

