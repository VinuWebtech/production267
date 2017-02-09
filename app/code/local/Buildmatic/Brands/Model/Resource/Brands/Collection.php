<?php

class Buildmatic_Brands_Model_Resource_Brands_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('buildmatic_brands/brands');
    }
}