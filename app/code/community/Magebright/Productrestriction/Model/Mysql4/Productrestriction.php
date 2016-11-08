<?php
class Magebright_Productrestriction_Model_Mysql4_Productrestriction extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {   
        $this->_init('productrestriction/productrestriction', 'productrestriction_id');
		$this->_isPkAutoIncrement = false;
    }
}