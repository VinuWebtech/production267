<?php
class Buildmatic_Brands_Model_Brands extends Mage_Core_Model_Abstract
{
	public function _construct()
	{
		parent::_construct();
		$this->_init('buildmatic_brands/brands');
	}
}