<?php
if (Mage::helper('request4quote')->isRentalInstalled()) {
	class ITwebexperts_Request4quote_Block_Cart_Item_Renderer_Configurable extends ITwebexperts_Payperrentals_Block_Checkout_Cart_Item_Renderer_Configurable {

        }
	
} else {
	
	class ITwebexperts_Request4quote_Block_Cart_Item_Renderer_Configurable extends Mage_Checkout_Block_Cart_Item_Renderer_Configurable {

	
	}
	
}