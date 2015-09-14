<?php
class Sz_Vendor_Model_Feedbackcount extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('vendor/feedbackcount');
    }
}