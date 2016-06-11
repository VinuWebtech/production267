<?php

class ITwebexperts_Request4quote_Model_Tax_Sales_Total_Quote_Tax extends Mage_Tax_Model_Sales_Total_Quote_Tax{

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        if(!method_exists($address->getQuote(), 'isR4q') || !$address->getQuote()->isR4q() || Mage::helper('request4quote')->isTaxEstimatesEnabled()) {
            parent::fetch($address);
        }

        return $this;
    }
}