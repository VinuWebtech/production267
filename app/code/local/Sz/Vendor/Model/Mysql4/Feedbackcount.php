<?php
class Sz_Vendor_Model_Mysql4_Feedbackcount extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('vendor/feedbackcount', 'feedcountid');
    }
}