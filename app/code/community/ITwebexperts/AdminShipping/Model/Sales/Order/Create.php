<?php

class ITwebexperts_AdminShipping_Model_Sales_Order_Create extends Mage_Adminhtml_Model_Sales_Order_Create
{
    /**
     * Parse data retrieved from request
     *
     * @param   array $data
     * @return  Mage_Adminhtml_Model_Sales_Order_Create
     */
    public function importPostData($data)
    {
        if (is_array($data)) {
            $this->addData($data);
        } else {
            return $this;
        }

        if (isset($data['account'])) {
            $this->setAccountData($data['account']);
        }

        if (isset($data['comment'])) {
            $this->getQuote()->addData($data['comment']);
            if (empty($data['comment']['customer_note_notify'])) {
                $this->getQuote()->setCustomerNoteNotify(false);
            } else {
                $this->getQuote()->setCustomerNoteNotify(true);
            }
        }

        if (isset($data['billing_address'])) {
            $this->setBillingAddress($data['billing_address']);
        }

        if (isset($data['shipping_address'])) {
            $this->setShippingAddress($data['shipping_address']);
        }

        if (isset($data['shipping_method'])) {
            $this->setShippingMethod($data['shipping_method']);
        }

        if (isset($data['payment_method'])) {
            $this->setPaymentMethod($data['payment_method']);
        }

        if($this->getShippingMethod() && $this->getShippingMethod() == 'adminshipping_adminshipping') {
            if (isset($data['shipping_amount'])) {
                $shippingPrice = $this->_parseShippingPrice($data['shipping_amount']);
                $this->getQuote()->setCustomPrice($shippingPrice);
            }

            if (isset($data['shipping_description'])) {
                $this->getQuote()->setCustomTitle($data['shipping_description']);
            }

            if ($this->getQuote()->getCustomPrice()) {
                $shippingPrice = $this->_parseShippingPrice($this->getQuote()->getCustomPrice());
                $this->getQuote()->getShippingAddress()->setShippingAmount($shippingPrice);
            }

            if ($this->getQuote()->getCustomPrice()) {
                $baseShippingPrice = $this->_parseShippingPrice($this->getQuote()->getCustomPrice());
                $this->getQuote()->getShippingAddress()->setBaseShippingAmount($baseShippingPrice, true);
            }

            if ($this->getQuote()->getCustomTitle()) {
                $this->getQuote()->getShippingAddress()->setShippingDescription($this->getQuote()->getCustomTitle());
            }

        }
        
        if ($this->getQuote()->getShippingAddress()->getShippingAmount()) {
            $this->getQuote()->setCustomPrice($this->getQuote()->getShippingAddress()->getShippingAmount());
        }

        if ($this->getQuote()->getShippingAddress()->getShippingDescription()) {
            $this->getQuote()->setCustomTitle($this->getQuote()->getShippingAddress()->getShippingDescription());
        }
        if (isset($data['coupon']['code'])) {
            $this->applyCoupon($data['coupon']['code']);
        }
        return $this;
    }

    protected function _parseShippingPrice($price)
    {
        $price = Mage::app()->getLocale()->getNumber($price);
        $price = $price>0 ? $price : 0;
        return $price;
    }

}
