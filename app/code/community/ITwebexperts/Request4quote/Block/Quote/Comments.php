<?php
class ITwebexperts_Request4quote_Block_Quote_Comments extends ITwebexperts_Request4quote_Block_Quote_Info_Abstract
{
    public function getTitle(){
        return $this->__('Quote Comments');
    }

    public function getR4qId()
    {
        return $this->getQuote()->getId();
    }

    public function getAddComment()
    {
        return $this->__('Add Comment To Quote');
    }

    public function getFormAction()
    {
        return $this->getUrl('request4quote_front/quote/submitcomment');
    }
}