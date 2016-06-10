<?php
class ITwebexperts_Request4quote_Block_Cart extends Mage_Checkout_Block_Cart
{

    public function getIsVirtual()
    {
        return $this->helper('request4quote/cart')->getIsVirtualQuote();
    }

    /**
     * Returns module helper.
     * @return ITwebexperts_Request4quote_Helper_Data
     */
    public function getModuleHelper()
    {
        return Mage::helper('request4quote');
    }

    public function getCheckout()
    {
        if (null === $this->_checkout) {
            $this->_checkout = Mage::getSingleton('request4quote/session');
        }
        return $this->_checkout;
    }

    public function getCustomer()
    {
        return Mage::helper('customer')->getCustomer();
    }

    public function getShippingAddress()
    {
        return $this->getCustomer()->getDefaultShippingAddress();
    }

    public function getEstimateCountryId()
    {
        if ($this->getShippingAddress()) {
            return $this->getShippingAddress()->getCountry();
        } else {
            return parent::getEstimateCountryId();
        }
    }

    /**
     * Checks if shipping address is required.
     * @return bool
     */
    public function isShippingAddressRequired()
    {
        return $this->getModuleHelper()->isShippingAddressRequiredCustomer();
    }

    /**
     * Checks if billing address is required.
     * @return bool
     */
    public function isBillingAddressRequired()
    {
        return $this->getModuleHelper()->isBillingAddressRequiredCustomer();
    }

    /**
     * Checks if billing address is enabled.
     * @return bool
     */
    public function canShowBillingAddress()
    {
        return $this->getModuleHelper()->canShowBillingAddressCustomer();
    }

    /**
     * Checks if shipping address is enabled.
     * @return bool
     */
    public function canShowShippingAddress()
    {
        return $this->getModuleHelper()->canShowShippingAddressCustomer();
    }

    /**
     * Returns the name of the class for js validator.
     *
     * @return string
     */
    public function getBillingRequiredEntryClass()
    {
        if ($this->isBillingAddressRequired()) {
            return ' required-entry';
        }
        return '';
    }

    /**
     * Returns the name of the class for js validator.
     *
     * @return string
     */
    public function getBillingRequiredClass()
    {
        if ($this->isBillingAddressRequired()) {
            return 'required';
        }
        return '';
    }

    /**
     * Returns the name of the class for js validator.
     *
     * @return string
     */
    public function getShippingRequiredEntryClass()
    {
        if ($this->isShippingAddressRequired()) {
            return ' required-entry';
        }
        return '';
    }

    /**
     * Returns the name of the class for js validator.
     *
     * @return string
     */
    public function getShippingRequiredClass()
    {
        if ($this->isShippingAddressRequired()) {
            return 'required';
        }
        return '';
    }
}