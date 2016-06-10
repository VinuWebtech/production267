<?php
class ITwebexperts_Request4quote_Block_Cart_Shipping extends Mage_Checkout_Block_Cart_Shipping
{
    /**
     * Returns active quote
     *
     * @return ITwebexperts_Request4quote_Model_Quote_Component
     */
    public function getQuote()
    {
        if (null === $this->_quote) {
            $this->_quote = $this->_getCartHelper()->getQuote();
        }
        return $this->_quote;
    }

    /**
     * Returns cart helper.
     *
     * @return ITwebexperts_Request4quote_Helper_Cart
     */
    protected function _getCartHelper()
    {
        return $this->helper('request4quote/cart');
    }

    /**
     * Returns default helper.
     *
     * @return ITwebexperts_Request4quote_Helper_Data
     */
    protected function _getModuleHelper()
    {
        return $this->helper('request4quote');
    }

    /**
     * Returns modified url in some cases.
     *
     * @param string $url
     * @return string
     */
    public function getUrl($url = '', $params = array())
    {
        // Not actually the best way of doing it, but I prefer not to create another template
        // just to change these 2 urls.
        if ('checkout/cart/estimateUpdatePost' == $url) {
            return parent::getUrl('request4quote_front/quote/estimateUpdatePost');
        }

        if ('checkout/cart/estimatePost' == $url) {
            return parent::getUrl('request4quote_front/quote/estimatePost');
        }

        return parent::getUrl($url, $params);
    }
}