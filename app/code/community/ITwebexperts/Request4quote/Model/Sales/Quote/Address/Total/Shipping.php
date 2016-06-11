<?php
if(Mage::helper('request4quote')->hasAdminShipping()){
    class ITwebexperts_Request4quote_Model_Sales_Quote_Address_Total_Shipping_Common extends ITwebexperts_AdminShipping_Model_Sales_Quote_Address_Total_Shipping{

    }
}else{
    class ITwebexperts_Request4quote_Model_Sales_Quote_Address_Total_Shipping_Common extends Mage_Sales_Model_Quote_Address_Total_Shipping{

    }
}
class ITwebexperts_Request4quote_Model_Sales_Quote_Address_Total_Shipping extends ITwebexperts_Request4quote_Model_Sales_Quote_Address_Total_Shipping_Common
{

    /**
	 * Add shipping totals information to address object
	 *
	 * @param   Mage_Sales_Model_Quote_Address $address
	 * @return  Mage_Sales_Model_Quote_Address_Total_Shipping
	 */
	public function fetch(Mage_Sales_Model_Quote_Address $address)
	{
		if(method_exists($address->getQuote(), 'isR4q') && $address->getQuote()->isR4q()) {
			$amount = $address->getShippingAmount();
			if ($amount != 0 || $address->getShippingDescription()) {
				$title = Mage::helper('sales')->__('Shipping & Handling');
				if ($address->getShippingDescription()) {
					$title .= ' (' . $address->getShippingDescription() . ')';
				}
				if (Mage::helper('request4quote')->isShippingQuotesEnabled() == true) {
					$address->addTotal(array(
						'code' => $this->getCode(),
						'title' => $title,
						'value' => $address->getShippingAmount()
					));
				}
			}
		}else{
			parent::fetch($address);
		}
		return $this;
	}
}
