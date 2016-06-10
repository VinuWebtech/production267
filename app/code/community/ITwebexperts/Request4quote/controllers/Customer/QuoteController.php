<?php

class ITwebexperts_Request4quote_Customer_QuoteController extends Mage_Core_Controller_Front_Action {
	
	protected function _getSession()
    {
        return Mage::getSingleton('request4quote/session');
    }
	
	protected function _initQuote($idFieldName = 'quote_id')
    {
        $this->_title($this->__('request4quote'))->_title($this->__('Manage Quote Requests'));
        $quoteId = (int) $this->getRequest()->getParam($idFieldName);
        $quote = Mage::getModel('request4quote/quote');
        if ($quoteId) {
            $quote->loadByIdWithoutStore($quoteId);
        }
		if (!$quote->getId() || !$quote->getCustomerId() || $quote->getCustomerId() != Mage::helper('customer')->getCustomer()->getId()) {
			return false;
		}
        Mage::register('current_quote', $quote);
        return $this;
    }
	
	public function preDispatch()
    {
        parent::preDispatch();
        $action = $this->getRequest()->getActionName();
        $loginUrl = Mage::helper('customer')->getLoginUrl();

        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }
	
	public function historyAction()
	{
		$this->loadLayout()
            ->_initLayoutMessages('request4quote/session')
            ->_initLayoutMessages('customer/session');
		$this->getLayout()->getBlock('head')->setTitle($this->__('My Quotes'));
		$this->renderLayout();
	}
	
	public function viewAction()
	{
		// init quote
		if (!$this->_initQuote()) {
			$this->_getSession()->addError($this->__('Access denied.'));
			$this->_redirect('request4quote_front/customer_quote/history');
			return;
		}
		// layout
		$this->loadLayout()
            ->_initLayoutMessages('request4quote/session')
            ->_initLayoutMessages('customer/session');
		$this->getLayout()->getBlock('head')->setTitle($this->__('Quote Details'));
		$navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('request4quote_front/customer_quote/history');
        }
		$this->renderLayout();
	}
	
}