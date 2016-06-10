<?php
class ITwebexperts_Request4quote_Model_Resource_Quote_Status_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
	
	protected function _construct()
    {
        $this->_init('request4quote/quote_status');
    }
	
	public function toOptionArray()
    {
        return $this->_toOptionArray('status', 'label');
    }
	
	public function toOptionHash()
    {
        return $this->_toOptionHash('status', 'label');
    }
	
	public function orderByLabel($dir = 'ASC')
    {
        $this->getSelect()->order('main_table.label '.$dir);
        return $this;
    }
}