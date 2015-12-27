<?php
class Sz_Vendor_Model_Mysql4_Userprofile_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('vendor/userprofile');
    }
}