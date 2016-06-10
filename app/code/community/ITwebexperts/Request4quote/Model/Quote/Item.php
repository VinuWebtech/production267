<?php

class ITwebexperts_Request4quote_Model_Quote_Item extends Mage_Sales_Model_Quote_Item {
    
    protected $_eventPrefix = 'request4quote_quote_item';
    protected $_eventObject = 'r4q_item';
    
    protected function _construct()
    {
        $this->_init('request4quote/quote_item');
        $this->_errorInfos = Mage::getModel('sales/status_list');
    }

    public function addOption($option)
    {
        if (is_array($option)) {
            $option = Mage::getModel('request4quote/quote_item_option')->setData($option)
                ->setItem($this);
        }
        elseif (($option instanceof Varien_Object) && !($option instanceof Mage_Sales_Model_Quote_Item_Option)) {
            $option = Mage::getModel('request4quote/quote_item_option')->setData($option->getData())
               ->setProduct($option->getProduct())
               ->setItem($this);
        }
        elseif($option instanceof Mage_Sales_Model_Quote_Item_Option) {
            $option->setItem($this);
        }
        else {
            Mage::throwException(Mage::helper('sales')->__('Invalid item option format.'));
        }

        if ($exOption = $this->getOptionByCode($option->getCode())) {
            $exOption->addData($option->getData());
        }
        else {
            $this->_addOptionCode($option);
            $this->_options[] = $option;
        }
        return $this;
    }
}
