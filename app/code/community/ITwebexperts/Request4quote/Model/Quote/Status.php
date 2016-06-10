<?php

class ITwebexperts_Request4quote_Model_Quote_Status extends Mage_Core_Model_Abstract {
    
    
    protected function _construct()
    {
        $this->_init('request4quote/quote_status');
    }
	
	public function getStoreLabels()
    {
        if ($this->hasData('store_labels')) {
            return $this->_getData('store_labels');
        }
        $labels = $this->_getResource()->getStoreLabels($this);
        $this->setData('store_labels', $labels);
        return $labels;
    }
	
	public function getStoreLabel($store=null)
    {
        $store = Mage::app()->getStore($store);
        $label = false;
        if (!$store->isAdmin()) {
            $labels = $this->getStoreLabels();
            if (isset($labels[$store->getId()])) {
                return $labels[$store->getId()];
            }
        }
        return Mage::helper('request4quote')->__($this->getLabel());
    }
	
	
}
