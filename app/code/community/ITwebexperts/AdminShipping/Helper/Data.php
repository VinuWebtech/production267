<?php

class ITwebexperts_AdminShipping_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isEnabled() {
        return Mage::getStoreConfig('carriers/adminshipping/active');
    }
    public function hasR4q(){
        return Mage::helper('core')->isModuleEnabled('ITwebexperts_Request4quote');
    }
}
