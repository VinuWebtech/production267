<?php
class Sz_Vendor_Model_Mysql4_Product extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('vendor/product', 'index_id');
    }
}