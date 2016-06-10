<?php
class ITwebexperts_Request4quote_Model_Resource_Quote_Item extends Mage_Sales_Model_Resource_Quote_Item
{
	protected function _construct()
    {
        $this->_init('request4quote/quote_item', 'item_id');
    }
}