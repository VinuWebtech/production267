<?php
class ITwebexperts_Request4quote_Block_Quote_History extends Mage_Core_Block_Template {
	
	public function __construct()
    {
        parent::__construct();
        $this->setTemplate('request4quote/quote/history.phtml');

        $quotes = Mage::getResourceModel('request4quote/quote_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
            ->addFieldToFilter('r4q_status', array('neq' => null))
            ->setOrder('created_at', 'desc')
        ;

        $this->setQuotes($quotes);

        Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('root')->setHeaderTitle(Mage::helper('request4quote')->__('My Quote Requests'));
    }
	
	protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $pager = $this->getLayout()->createBlock('page/html_pager', 'r4q.quote.history.pager')
            ->setCollection($this->getQuotes());
        $this->setChild('pager', $pager);
        $this->getQuotes()->load();
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getViewUrl($quote)
    {
        return $this->getUrl('*/*/view', array('quote_id' => $quote->getId()));
    }
}