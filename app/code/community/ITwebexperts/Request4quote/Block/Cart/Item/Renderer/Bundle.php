<?php
if (Mage::helper('request4quote')->isRentalInstalled()) {


	class ITwebexperts_Request4quote_Block_Cart_Item_Renderer_Bundle extends ITwebexperts_Payperrentals_Block_Checkout_Cart_Item_Renderer_Bundle {

	
	}
	
} else {
	
	class ITwebexperts_Request4quote_Block_Cart_Item_Renderer_Bundle extends Mage_Bundle_Block_Checkout_Cart_Item_Renderer {

	
	}
	
}