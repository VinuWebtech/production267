<?php

if (Mage::helper('itwebcommon')->hasWarehouse()){
    class ITwebexperts_Request4quote_Model_Quote_Item_Option_Component extends Innoexts_Warehouse_Model_Sales_Quote_Item_Option{

    }

}else{
    class ITwebexperts_Request4quote_Model_Quote_Item_Option_Component extends Mage_Sales_Model_Quote_Item_Option
    {

    }
}

    class ITwebexperts_Request4quote_Model_Quote_Item_Option extends ITwebexperts_Request4quote_Model_Quote_Item_Option_Component {

        protected function _construct()
        {
            $this->_init('request4quote/quote_item_option');
        }
    }
