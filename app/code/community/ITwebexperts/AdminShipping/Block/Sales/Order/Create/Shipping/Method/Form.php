<?php


class ITwebexperts_AdminShipping_Block_Sales_Order_Create_Shipping_Method_Form extends Mage_Adminhtml_Block_Sales_Order_Create_Shipping_Method_Form
{
    protected $_code    = 'adminshipping_adminshipping';

    /**
     * Retrieve custom shipping price
     *
     * @return float | false
     */
    public function getCustomShippingPrice()
    {
        if ($this->getShippingMethod() == $this->_code) {
            return $this->getQuote()->getCustomPrice();
        }

        return false;
    }

    /**
     * Retrieve custom shipping title
     *
     * @return string | false
     */
    public function getCustomTitle()
    {
        if ($this->getShippingMethod() == $this->_code) {
            return $this->getQuote()->getCustomTitle();
        }

        return false;
    } 

}
