<?php
class ITwebexperts_Request4quote_Block_Quote_Item_Renderer_Grouped extends ITwebexperts_Request4quote_Block_Quote_Item_Renderer_Default {
	
	
	protected function _toHtml()
    {
        $item = $this->getItem();
        if ($productType = $item->getRealProductType()) {
            $renderer = $this->getRenderedBlock()->getItemRenderer($productType);
            $renderer->setItem($this->getItem());
            return $renderer->toHtml();
        }
        return parent::_toHtml();
    }
	
}