<?php
class ITwebexperts_Request4quote_Block_Links extends Mage_Core_Block_Template
{
	
	public function addQuoteLink()
    {
        $parentBlock = $this->getParentBlock();
        if ($parentBlock && Mage::helper('core')->isModuleOutputEnabled('ITwebexperts_Request4quote')) {
            $count = $this->getSummaryQty() ? $this->getSummaryQty()
                : $this->helper('request4quote/cart')->getSummaryCount();
            if ($count == 1) {
                $text = $this->__('My Quote (%s item)', $count);
            } elseif ($count > 0) {
                $text = $this->__('My Quote (%s items)', $count);
            } else {
                $text = $this->__('My Quote');
            }

            $parentBlock->removeLinkByUrl($this->getUrl('request4quote_front/quote'));
            $isShowQuoteOnNav = Mage::getStoreConfig('request4quote/general/allowshowquotenav');
            if($isShowQuoteOnNav){
                $parentBlock->addLink($text, 'request4quote_front/quote', $text, true, array(), 50, null, 'class="top-link-quote"');
            }
        }
        return $this;
    }
	
}