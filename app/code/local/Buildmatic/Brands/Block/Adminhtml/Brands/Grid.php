<?php
/**
* 
*/
class Buildmatic_Brands_Block_Adminhtml_Brands_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	
	public function __construct()
	{
		parent::__construct();
		$this->setId('brandsGrid');
		$this->setDefaultSort('id');
		$this->setDefautDir('ASC');
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('buildmatic_brands/brands')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('id', array(
			'header' => Mage::helper('buildmatic_brands')->__('ID'),
			'align' => 'right',
			'width' => '10px',
			'index' => 'id',
		));

		$this->addColumn('brand_name', array(
			'header' => Mage::helper('buildmatic_brands')->__('Brand Name'),
			'align' => 'left',
			'width' => '50px',
			'index' => 'brand_name',
		));

		$this->addColumn('meta_title', array(
			'header' => Mage::helper('buildmatic_brands')->__('Meta Title'),
			'width' => '150px',
			'index' => 'meta_title',
		));

		$this->addColumn('url_key', array(
			'header' => Mage::helper('buildmatic_brands')->__('Url Key'),
			'width' => '150px',
			'index' => 'url_key',
		));
		return parent::_prepareColumns();
	}

	public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}