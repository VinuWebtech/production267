<?php
class ITwebexperts_Request4quote_Block_Adminhtml_Quote_Grid extends Mage_Adminhtml_Block_Widget_Grid {
	
	public function __construct()
    {
        parent::__construct();
		$this->setId('request4quote_quote_grid');
			$this->setUseAjax(false);
			$this->setDefaultSort('created_at');
			$this->setDefaultDir('DESC');
			$this->setSaveParametersInSession(true);
	}
	
	
	
	protected function _getCollectionClass()
    {
        return 'request4quote/quote_collection';
    }
	
	
	protected function _prepareCollection()
    {
        /** @var $collection ITwebexperts_Request4quote_Model_Resource_Quote_Collection */
        $collection = Mage::getResourceModel($this->_getCollectionClass());
		$collection->addFieldToFilter('r4q_status', array('neq' => 'NULL'));
		$collection->addShippingAddress();
        $collection->getSelect()->joinLeft(array('users'=>Mage::getSingleton('core/resource')->getTableName('admin/user')),'main_table.salesrep=users.user_id');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('request4quote');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('request4quote')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('request4quote')->__('Are you sure?')
        ));

        $statusoptions = Mage::getResourceModel('request4quote/quote_status')->getOptionArray();
        array_unshift($statusoptions, array('label'=>'','value'=>''));

        $this->getMassactionBlock()->addItem('status', array(
            'label' =>  Mage::helper('request4quote')->__('Update Status'),
            'url'   =>  Mage::helper('adminhtml')->getUrl('*/*/massChangeStatus', array('_current'=>true)),
            'additional'    =>  array(
                'visibility'    =>  array(
                    'name'  =>  'status',
                    'type'  =>  'select',
                    'class' =>  'required-entry',
                    'label' =>  Mage::helper('request4quote')->__("Status"),
                    'values'    =>  $statusoptions
                )
            )
        ));

        return $this;
    }

    /**
     * @param $collection
     * @param $column
     */
    protected function _filterCustomerCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->getSelect()->where('concat(main_table.customer_firstname," ",main_table.customer_lastname) LIKE ?', '%'.$value.'%');
    }
	
	
	protected function _prepareColumns()
    {

        $this->addColumn('entity_id', array(
            'header'=> Mage::helper('request4quote')->__('Quote #'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'entity_id',
        ));
		
		
		$this->addColumn('created_at', array(
            'header' => Mage::helper('request4quote')->__('Created On'),
            'index' => 'created_at',
            'filter_index' => 'main_table.created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));


        $this->addColumn('customer_firstname', array(
            'header' => Mage::helper('request4quote')->__('Customer Name'),
            'index' => 'customer_firstname',
            'filter_index' => 'entity_id',
            'filter_condition_callback' => array($this, '_filterCustomerCondition'),
            'renderer'  =>  'ITwebexperts_Request4quote_Block_Adminhtml_Quote_Render_Customername'
        ));

        $this->addColumn('company', array(
            'header' => Mage::helper('request4quote')->__('Company'),
            'index' => 'company'
        ));

		
		$this->addColumn('customer_email', array(
            'header' => Mage::helper('request4quote')->__('Email'),
            'index' => 'customer_email',
        ));
		
		$this->addColumn('r4q_phone', array(
            'header' => Mage::helper('request4quote')->__('Phone Number'),
            'index' => 'r4q_phone',
        ));

//		$this->addColumn('shipping_country_id', array(
//            'header' => Mage::helper('request4quote')->__('Country'),
//            'index' => 'shipping_country_id',
//        ));
		
//		$this->addColumn('shipping_region', array(
//            'header' => Mage::helper('request4quote')->__('State/Province'),
//            'index' => 'shipping_region',
//        ));
        if(Mage::helper('request4quote')->isRentalInstalled()){
            $this->addColumn('start_datetime', array(
                'header' => Mage::helper('request4quote')->__('Start Date'),
                'index' => 'start_datetime',
                'renderer' => 'payperrentals/adminhtml_html_renderer_datetime',
                'width' => '120px',
                'type' => 'datetime'
            ));

            $this->addColumn('end_datetime', array(
                'header' => Mage::helper('request4quote')->__('End Date'),
                'index' => 'end_datetime',
                'renderer' => 'payperrentals/adminhtml_html_renderer_datetime',
                'width' => '120px',
                'type' => 'datetime'
            ));
        }
		
		$this->addColumn('city', array(
            'header' => Mage::helper('request4quote')->__('City'),
            'index' => 'city',
        ));
		
		$this->addColumn('postcode', array(
            'header' => Mage::helper('request4quote')->__('Zip'),
            'index' => 'postcode',
        ));
		
		$this->addColumn('street', array(
            'header' => Mage::helper('request4quote')->__('Street'),
            'index' => 'street',
        ));

        $this->addColumn('r4q_status', array(
            'header' => Mage::helper('request4quote')->__('Status'),
            'index' => 'r4q_status',
            'width' => '70px',
        ));

        $this->addColumn('salesrep', array(
            'header'    =>  Mage::helper('request4quote')->__('Sales Rep'),
            'index' =>  'salesrep',
            'renderer'  =>  'ITwebexperts_Request4quote_Block_Adminhtml_Quote_Render_Adminname'
        ));

        

        return parent::_prepareColumns();
    }
	
	
	public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/quote_edit/start', array('quote_id' => $row->getId()));
    }
	
	
}