<?php
class ITwebexperts_Request4quote_Block_Adminhtml_Quote_Status_Grid extends Mage_Adminhtml_Block_Widget_Grid {
	
	public function __construct()
    {
        parent::__construct();
        $this->setId('quote_status_grid');
        //$this->setFilterVisibility(false);
        $this->setPagerVisibility(true);
        $this->setDefaultSort('status');
        $this->setDefaultDir('DESC');
    }
	
	protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('request4quote/quote_status_collection');
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }
	
	protected function _prepareColumns()
    {
        $this->addColumn('label', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'label',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status Code'),
            'type'  => 'text',
            'index' => 'status',
            'filter_index' => 'main_table.status',
            'width'     => '200px',
        ));


        $this->addColumn('is_system', array(
            'header'    => Mage::helper('sales')->__('Is System'),
            'index'     => 'is_system',
            'width'     => '100px',
            'type'      => 'text',
        ));
		
		$this->addColumn('edit_remove', array(
            'header'    => Mage::helper('sales')->__('Action'),
            'index'     => 'edit_remove',
            'width'     => '100px',
            'type'      => 'text',
            'frame_callback' => array($this, 'decorateAction'),
            'sortable'  => false,
            'filter'    => false,
        ));

        return parent::_prepareColumns();
    }
	
	public function decorateAction($value, $row, $column, $isExport)
    {
        $cell = '';
        if (!$row->getIsSystem()) {
			$removeUrl = $this->getUrl(
                '*/*/remove',
                array('status' => $row->getStatus())
            );
			$label = Mage::helper('request4quote')->__('Remove');
			$cell .= '<a href="' . $removeUrl . '">' . $label . '</a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;';
		}
		$editUrl = $this->getUrl(
			'*/*/edit',
			array('status' => $row->getStatus())
		);
		$label = Mage::helper('request4quote')->__('Edit');
		$cell .= '<a href="' . $editUrl . '">' . $label . '</a>';
        return $cell;
    }
	
	public function getRowUrl($row)
    {
        return $this->getUrl('*/quote_status/edit', array('status' => $row->getStatus()));
    }
}
