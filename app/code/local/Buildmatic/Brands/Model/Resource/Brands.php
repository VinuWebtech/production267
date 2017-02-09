<?php 
class Buildmatic_Brands_Model_Resource_Brands extends Mage_Core_Model_Resource_Db_Abstract
{
	public function _construct()
	{
		$this->_init('buildmatic_brands/brands', 'id'); //id is the primary key of table
	}
}