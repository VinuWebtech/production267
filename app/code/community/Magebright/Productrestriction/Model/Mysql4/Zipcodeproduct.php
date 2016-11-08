<?php
class Magebright_Productrestriction_Model_Mysql4_Zipcodeproduct extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {   
        $this->_init('productrestriction/zipcodeproduct', 'zipcodeproduct_id');
		$this->_isPkAutoIncrement = false;
    }
}