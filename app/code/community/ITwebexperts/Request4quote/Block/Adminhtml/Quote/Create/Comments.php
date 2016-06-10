<?php
class ITwebexperts_Request4quote_Block_Adminhtml_Quote_Create_Comments extends ITwebexperts_Request4quote_Block_Adminhtml_Quote_Create_Abstract
{
    protected function _prepareLayout()
    {
        $onclick = "submitAndReloadArea($('order_history_block').parentNode, '".$this->getSubmitUrl()."')";
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => Mage::helper('request4quote')->__('Submit Comment'),
                'class'   => 'save',
                'onclick'   =>   $onclick
            ));
        $this->setChild('submit_button', $button);

        return parent::_prepareLayout();
    }


    public function getHeaderText(){
        return Mage::helper('request4Quote')->__('Comments & Quote Status');
    }

    public function getHeaderCssClass()
    {
        return 'head-account';
    }

    public function escapeHtml($data, $allowedTags = null)
    {
        if (is_string($data) && is_array($allowedTags) && in_array('a', $allowedTags)) {
            $links = array();
            $i = 1;
            $regexp = '@(<a[^>]*>(?:[^<]|<[^/]|</[^a]|</a[^>])*</a>)@';
            while (preg_match($regexp, $data, $matches)) {
                $links[] = $matches[1];
                $data = str_replace($matches[1], '%' . $i . '$s', $data);
                ++$i;
            }
            $data = Mage::helper('core')->escapeHtml($data, $allowedTags);
            return vsprintf($data, $links);
        }
    }

    public function getR4qId(){
        return $this->getQuote()->getId();
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('*/quote/addComment'/*,array('id'=>$this->getEntity()->getId())*/);
    }

}