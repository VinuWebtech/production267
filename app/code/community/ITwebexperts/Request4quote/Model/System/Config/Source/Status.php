<?php
class ITwebexperts_Request4quote_Model_System_Config_Source_Status
{
    public function toOptionArray()
    {
        $options =  array();

        foreach (Mage::getSingleton('request4quote/quote_status')->getCollection() as $status) {
            $options[] = array(
                'value' => $status->getId(),
                'label' => $status->getStoreLabel()
            );
        }

        return $options;
    }
}