<?php
class ITwebexperts_Request4quote_Block_Adminhtml_Quote_Edit_Items_Renderer_Default extends Mage_Adminhtml_Block_Sales_Order_View_Items_Renderer_Default {
	
	public function getOrder()
	{
		return Mage::registry('current_quote');
	}
	
	public function canDisplayGiftmessage()
    {
        return false;
    }
	
	public function displayRoundedPrices($basePrice, $price, $precision=2, $strong = false, $separator = '<br />')
    {
        $res = $this->getOrder()->formatPricePrecision($price, $precision);
		if ($strong) {
			$res = '<strong>'.$res.'</strong>';
		}
        return $res;
    }
	
	public function getPriceProposal()
	{
		if (!is_null($this->getItem()->getR4qPriceProposal())) {
			return $this->getItem()->getR4qPriceProposal();
		} else {
			return $this->getItem()->getPrice();
		}
	}
	
	
}