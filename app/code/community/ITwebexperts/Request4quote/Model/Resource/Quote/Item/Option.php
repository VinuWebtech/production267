<?php
class ITwebexperts_Request4quote_Model_Resource_Quote_Item_Option extends Mage_Sales_Model_Resource_Quote_Item_Option {
	
	protected function _construct()
    {
        $this->_init('request4quote/quote_item_option', 'option_id');
    }
	
	protected function _assignOptions()
    {
        $itemIds          = array_keys($this->_items);
        $optionCollection = Mage::getModel('request4quote/quote_item_option')->getCollection()
            ->addItemFilter($itemIds);
        foreach ($this as $item) {
            $item->setOptions($optionCollection->getOptionsByItem($item));
        }
        $productIds        = $optionCollection->getProductIds();
        $this->_productIds = array_merge($this->_productIds, $productIds);

        return $this;
    }
	
}