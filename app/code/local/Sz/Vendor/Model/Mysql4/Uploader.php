<?php
class Sz_Vendor_Model_Mysql4_Uploader extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('vendor/uploader', 'id');
    }
}