<?php

class ITwebexperts_AdminShipping_Model_Carrier_Adminshipping
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'adminshipping';
    protected $_isFixed = true;


    private function _isActive(){
        if(Mage::app()->getStore()->isAdmin()){
            return true;
        }
        if(Mage::helper('adminshipping')->hasR4q()) {
            $quoteInCartId = Mage::helper('request4quote')->cartHasQuoteItems();
            if ($quoteInCartId) {
                $quote = Mage::getModel('request4quote/quote')->loadByIdWithoutStore($quoteInCartId);
                $shipMethod = $quote->getShippingAddress()->getShippingMethod();
                if($shipMethod == 'adminshipping_adminshipping'){
                    return true;
                }
            }
            return false;
        }else{
            return Mage::app()->getStore()->isAdmin();
        }

    }

    /**
     * Admin Shipping Rates Collector
     *
     * @param Mage_Shipping_Model_Rate_Request $data
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active') || !$this->_isActive()) {
            return false;
        }

        $result = Mage::getModel('shipping/rate_result');

        $method = Mage::getModel('shipping/rate_result_method');
        /**
         * try catch block added because is_object didn't work.
         */
        try {
            $r4qSession = Mage::getSingleton('request4quote/adminhtml_session_quote');
        }catch(Exception $e){
            $r4qSession = 0;
        }
        if(Mage::getSingleton('adminhtml/session_quote')->getOrder()->getShippingDescription()) {
            $method->setCarrier($this->_code);
            $method->setCarrierTitle(Mage::helper('shipping')->__('Admin Shipping'));

            $method->setMethod($this->_code);
            $method->setMethodTitle(Mage::getSingleton('adminhtml/session_quote')->getOrder()->getShippingDescription());
        }
        else if (is_object($r4qSession) && $r4qSession->getQuoteId()) {
            $method->setCarrier($this->_code);
            $method->setCarrierTitle(Mage::helper('shipping')->__('Admin Shipping'));

            $method->setMethod($this->_code);
            if($r4qSession->getQuote()->getShippingAddress()->getShippingDescription()) {
                $method->setMethodTitle($r4qSession->getQuote()->getShippingAddress()->getShippingDescription());
            }else{
                $quote = Mage::getModel('request4quote/quote')->loadByIdWithoutStore($r4qSession->getQuoteId());
                $method->setMethodTitle($quote->getShippingAddress()->getShippingDescription());
            }
        }
        else{
            $method->setCarrier($this->_code);
            $method->setCarrierTitle(Mage::helper('shipping')->__('Custom Carrier'));

            $method->setMethod($this->_code);
            $method->setMethodTitle(Mage::helper('shipping')->__('Custom Method'));
        }
        $method->setPrice(0);
        $method->setCost(0);
        if(Mage::helper('adminshipping')->hasR4q() && !Mage::app()->getStore()->isAdmin()) {
            $quoteInCartId = Mage::helper('request4quote')->cartHasQuoteItems();
            if ($quoteInCartId) {
                $quote = Mage::getModel('request4quote/quote')->loadByIdWithoutStore($quoteInCartId);
                $method->setPrice($quote->getShippingAddress()->getShippingAmount());
                $method->setCost($quote->getShippingAddress()->getShippingAmount());
                $method->setCarrier($this->_code);
                $method->setCarrierTitle(Mage::helper('shipping')->__('Custom Shipping'));

                $method->setMethod($this->_code);
                $method->setMethodTitle($quote->getShippingAddress()->getShippingDescription());

            }
        } else if (is_object($r4qSession) && $r4qSession->getQuoteId()) {
            if($r4qSession->getQuote()->getShippingAddress()->getShippingAmount()) {
                $method->setPrice($r4qSession->getQuote()->getShippingAddress()->getShippingAmount());
                $method->setCost($r4qSession->getQuote()->getShippingAddress()->getShippingAmount());
            }else{
                $quote = Mage::getModel('request4quote/quote')->loadByIdWithoutStore($r4qSession->getQuoteId());
                $method->setPrice($quote->getShippingAddress()->getShippingAmount());
                $method->setCost($quote->getShippingAddress()->getShippingAmount());
            }
        }
        else if(is_object(Mage::getSingleton('adminhtml/session_quote')->getOrder()) && Mage::getSingleton('adminhtml/session_quote')->getOrder()->getShippingAmount()) {
            $method->setPrice(Mage::getSingleton('adminhtml/session_quote')->getOrder()->getShippingAmount());
            $method->setCost(Mage::getSingleton('adminhtml/session_quote')->getOrder()->getShippingAmount());
        }

        $result->append($method);

        return $result;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array('adminshipping'=>Mage::helper('shipping')->__('Admin Shipping Rate'));
    }

}