<?php
class ITwebexperts_Request4quote_Model_Resource_Comments extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('request4quote/comments', 'entity_id');
    }
}