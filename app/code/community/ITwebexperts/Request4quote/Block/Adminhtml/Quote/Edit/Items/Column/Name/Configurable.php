<?php
if (Mage::helper('request4quote')->isRentalInstalled()) {
	
	class ITwebexperts_Request4quote_Block_Adminhtml_Quote_Edit_Items_Column_Name_Configurable extends ITwebexperts_Request4quote_Block_Adminhtml_Quote_Edit_Items_Column_Name {
		
		public function getOrderOptions()
		{
			$helper = Mage::helper('catalog/product_configuration');
			$options = $helper->getConfigurableOptions($this->getItem());
			
			$infoByRequest = $this->getItem()->getOptionByCode('info_buyRequest');
			if ($infoByRequest) {
				$info = unserialize($infoByRequest->getValue());
				if(isset($info [ITwebexperts_Payperrentals_Model_Product_Type_Reservation::START_DATE_OPTION])){
					$start_date = @$info[ITwebexperts_Payperrentals_Model_Product_Type_Reservation::START_DATE_OPTION];
					$end_date =  @$info[ITwebexperts_Payperrentals_Model_Product_Type_Reservation::END_DATE_OPTION];
					
					$start_date = ITwebexperts_Payperrentals_Helper_Data::formatDbDate($start_date,false,false);
					$end_date = ITwebexperts_Payperrentals_Helper_Data::formatDbDate($end_date,false,false);
					
					$options[] = array(
						'label' => $this->__('Start Date:'),
						'value'=> $start_date
					);
	
					$options[] = array(
						'label' => $this->__('End Date:'),
						'value' => $end_date
	
					);
				}
			}
			
			return $options;
		}
		
	}
	
} else {
	
	class ITwebexperts_Request4quote_Block_Adminhtml_Quote_Edit_Items_Column_Name_Configurable extends ITwebexperts_Request4quote_Block_Adminhtml_Quote_Edit_Items_Column_Name {
		
		public function getOrderOptions()
		{
			$helper = Mage::helper('catalog/product_configuration');
			$options = $helper->getConfigurableOptions($this->getItem());
			return $options;
		}
		
	}
	
}
