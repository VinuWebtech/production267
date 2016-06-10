<?php
class ITwebexperts_Request4quote_Block_Adminhtml_Quote_Edit_Items_Column_Qty extends Mage_Adminhtml_Block_Sales_Items_Column_Qty {
	
	public function getItem()
    {
        return $this->_getData('item');
    }
}