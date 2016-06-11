<?php
class ITwebexperts_Request4quote_Block_Quote_Info_Messages extends ITwebexperts_Request4quote_Block_Quote_Info_Abstract {
	
	protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('request4quote/quote/info/messages.phtml');
    }
	
}
