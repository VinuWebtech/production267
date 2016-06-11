<?php
class ITwebexperts_Request4quote_Model_Config_Version extends Mage_Core_Model_Config_Data
{
    protected function _afterLoad() {
        $this->setValue( (string)Mage::getConfig()->getNode()->modules->ITwebexperts_Request4quote->version );
    }
}
