<?php
class ITwebexperts_Request4quote_Block_Payperrentals_Sales_Order_Items_Bundle extends ITwebexperts_Payperrentals_Block_Sales_Order_Items_Renderer_Bundle {
	
	public function getOrderItem()
	{
		$item = $this->getItem();
		$helper = Mage::helper('bundle/catalog_product_configuration');
		$options = $helper->getOptions($item);
		foreach ($options AS &$option) {
			if (is_array($option['value'])) {
				if (isset($option['value'][0])) {
					$option['value'] = strip_tags($option['value'][0]);
				}
			}
		}
		if ($item->getBuyRequest()) {
			$item->setProductOptions(array('info_buyRequest' => $item->getBuyRequest()->toArray(), 'additional_options' => $options));
		}
		return $this->getItem();
	}
}