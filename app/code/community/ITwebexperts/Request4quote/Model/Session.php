<?php
class ITwebexperts_Request4quote_Model_Session extends Mage_Checkout_Model_Session {
	
	public function __construct()
    {
        $this->init('request4quote');
    }
	
	public function getQuote()
    {
        Mage::dispatchEvent('r4q_custom_quote_process', array('checkout_session' => $this));

        if ($this->_quote === null) {
            /** @var $quote Mage_Sales_Model_Quote */
            $quote = Mage::getModel('request4quote/quote')->setStoreId(Mage::app()->getStore()->getId());
            if ($this->getQuoteId()) {
                if ($this->_loadInactive) {
                    $quote->load($this->getQuoteId());
                } else {
                    $quote->loadActive($this->getQuoteId());
                }
                if ($quote->getId()) {
                    /**
                     * If current currency code of quote is not equal current currency code of store,
                     * need recalculate totals of quote. It is possible if customer use currency switcher or
                     * store switcher.
                     */
                    if ($quote->getQuoteCurrencyCode() != Mage::app()->getStore()->getCurrentCurrencyCode()) {
                        $quote->setStore(Mage::app()->getStore());
                        $quote->collectTotals()->save();
                        /*
                         * We mast to create new quote object, because collectTotals()
                         * can to create links with other objects.
                         */
                        $quote = Mage::getModel('request4quote/quote')->setStoreId(Mage::app()->getStore()->getId());
                        $quote->load($this->getQuoteId());
                    }
                } else {
                    $this->setQuoteId(null);
                }
            }

            $customerSession = Mage::getSingleton('customer/session');

            if (!$this->getQuoteId()) {
                if ($customerSession->isLoggedIn() || $this->_customer) {
                    $customer = ($this->_customer) ? $this->_customer : $customerSession->getCustomer();
                    $quote->loadByCustomer($customer);
                    $this->setQuoteId($quote->getId());
                } else {
                    $quote->setIsCheckoutCart(true);
                    Mage::dispatchEvent('r4q_checkout_quote_init', array('quote'=>$quote));
                }
            }

            if ($this->getQuoteId()) {
                if ($customerSession->isLoggedIn() || $this->_customer) {
                    $customer = ($this->_customer) ? $this->_customer : $customerSession->getCustomer();
                    $quote->setCustomer($customer);
                }
            }

            $quote->setStore(Mage::app()->getStore());
            $this->_quote = $quote;
        }

        if ($remoteAddr = Mage::helper('core/http')->getRemoteAddr()) {
            $this->_quote->setRemoteIp($remoteAddr);
            $xForwardIp = Mage::app()->getRequest()->getServer('HTTP_X_FORWARDED_FOR');
            $this->_quote->setXForwardedFor($xForwardIp);
        }
        return $this->_quote;
    }
	
	protected function _getQuoteIdKey()
    {
        return 'r4q_quote_id_' . Mage::app()->getStore()->getWebsiteId();
    }
	
	public function loadCustomerQuote()
    {
        if (!Mage::getSingleton('customer/session')->getCustomerId()) {
            return $this;
        }

        Mage::dispatchEvent('r4q_load_customer_quote_before', array('checkout_session' => $this));

        $customerQuote = Mage::getModel('request4quote/quote')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->loadByCustomer(Mage::getSingleton('customer/session')->getCustomerId());

        if ($customerQuote->getId() && $this->getQuoteId() != $customerQuote->getId()) {
            if ($this->getQuoteId()) {
                $customerQuote->merge($this->getQuote())
                    ->collectTotals()
                    ->save();
            }

            $this->setQuoteId($customerQuote->getId());

            if ($this->_quote) {
                $this->_quote->delete();
            }
            $this->_quote = $customerQuote;
        } else {
            $this->getQuote()->getBillingAddress();
            $this->getQuote()->getShippingAddress();
            $this->getQuote()->setCustomer(Mage::getSingleton('customer/session')->getCustomer())
                ->setTotalsCollectedFlag(false)
                ->collectTotals()
                ->save();
        }
        return $this;
    }
	
	public function clear()
    {
        Mage::dispatchEvent('r4q_checkout_quote_destroy', array('quote'=>$this->getQuote()));
        $this->_quote = null;
        $this->setQuoteId(null);
        $this->setLastSuccessQuoteId(null);
    }
	
}