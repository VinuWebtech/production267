<?php
    class Magebright_Productrestriction_Block_Adminhtml_Productrestriction_Grid extends Mage_Adminhtml_Block_Widget_Grid
    {
        public function __construct()
        {
            parent::__construct();
            $this->setId('productrestrictionGrid');
            // This is the primary key of the database
            $this->setDefaultSort('productrestriction_id');
            $this->setDefaultDir('ASC');
            $this->setSaveParametersInSession(true);
			$this->setUseAjax(true);
        }
     	 protected function _prepareCollection() {

        $collection = Mage::getModel('productrestriction/productrestriction')->getCollection();

            /*foreach($collection as $link){
		        if($link->getStoreId() && $link->getStoreId() != 0 )
		        {
		          $link->setStoreId(explode(',',$link->getStoreId()));
			    } else
			    {
			            $link->setStoreId(array('0'));
			    }
	    	}  */
          //echo "come at..here2";
         // exit;      
		$session = Mage::getSingleton('adminhtml/session');
		if($this->getRequest()->getParam('dir'))
			$dir=$this->getRequest()->getParam('dir');
		else
			$dir=(($productrestrictionGrid=$session->getData('productrestrictionGrid')) ? $productrestrictionGrid : 'DESC');

		if($session->getData('productrestrictionGridsort'))
			$productrestrictionGridsort = $session->getData('productrestrictionGridsort');
		else 
			$productrestrictionGridsort = 'productrestriction_id';

		if($sort=$this->getRequest()->getParam('sort'))
			$collection->getSelect()->order("$sort $dir");
		else
			$collection->getSelect()->order("$productrestrictionGridsort $dir");
			
              
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

     
        protected function _prepareColumns() {
        $this->addColumn('productrestriction_id', array(
            'header' => Mage::helper('productrestriction')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'productrestriction_id',
            'filter_index' => 'main_table.productrestriction_id',
            'type'  => 'number',
            'sortable'  => true
        ));
        $this->addColumn('pin_code', array(
            'header' => Mage::helper('productrestriction')->__('Zipcode'),
            'align' => 'left',
            'index' => 'pin_code',
             'filter_index' => 'main_table.pin_code',
             'sortable'  => true 
        ));
		
		$this->addColumn('city', array(
            'header' => Mage::helper('productrestriction')->__('City'),
            'align' => 'left',
            'index' => 'city',
            'filter_index' => 'main_table.city',
            'sortable'  => true 
        ));
        
         $this->addColumn('cod', array(
          'header'    => Mage::helper('productrestriction')->__('COD'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'cod',
          'type'      => 'options',
          'options'   => array(
              1 => 'Yes',
              0 => 'No', 
          ),
      ));
    
        $this->addColumn('delivery_days', array(
            'header' => Mage::helper('productrestriction')->__('Delivery Days'),
            'align' => 'left',
            'index' => 'delivery_days',
            'filter_index' => 'main_table.delivery_days',
            'sortable'  => true 
        ));
        
  /*   if ( !Mage::app()->isSingleStoreMode() ) {
	    $this->addColumn('store_id', array(
	        'header' => Mage::helper('productrestriction')->__('Store View'),
	        'index' => 'store_id',
	        'type' => 'store',
	        'store_all' => true,
	        'store_view' => true,
	        'sortable' => true,
	        'filter_condition_callback' => array($this, '_filterStoreCondition'),
	    ));
	}
       */ 
     
          
        
      
       
       
        $this->addColumn('action',
                array(
                    'header' => Mage::helper('productrestriction')->__('Action'),
                    'width' => '100',
                    'type' => 'action',
                    'getter' => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('productrestriction')->__('Delete'),
                        'url'       => array('base'=> '*/*/delete'),
                        'field'     => 'productrestriction_id',
                        'confirm'  => Mage::helper('productrestriction')->__('Are you sure?')
                    )
                ),
                    'filter' => false,
                    'sortable' => false,
                    'index' => 'stores',
                    'is_system' => true,
        ));
        $this->addExportType('*/*/exportCsv', Mage::helper('productrestriction')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('productrestriction')->__('XML'));

        return parent::_prepareColumns();
    }
        protected function _prepareMassaction()
        {
            $this->setMassactionIdField('productrestriction_id');
            $this->getMassactionBlock()->setFormFieldName('productrestriction');

            $this->getMassactionBlock()->addItem('delete', array(
                 'label'    => Mage::helper('productrestriction')->__('Delete'),
                 'url'      => $this->getUrl('*/*/massDelete'),
                 'confirm'  => Mage::helper('productrestriction')->__('Are you sure?')
            ));

           // $statuses = Mage::getSingleton('productrestriction/status')->getOptionArray();

           
            return $this;
        }
        
     /* protected function _filterStoreCondition($collection, $column)
		{
		    if ( !$value = $column->getFilter()->getValue() ) {
		        return;
		    }
		    $this->getCollection()->addStoreFilter($value);
		}*/
     
        public function getRowUrl($row)
        {
            return $this->getUrl('*/*/edit', array('id' => $row->getId()));
        }
        public function getAllManu()     {       
            $product = Mage::getModel('catalog/product');       
            $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                ->setEntityTypeFilter($product->getResource()->getTypeId())
                ->addFieldToFilter('attribute_code', 'productrestriction');      
            $attribute = $attributes->getFirstItem()->setEntity($product->getResource());       
            $productrestriction = $attribute->getSource()->getAllOptions(false);      
             return $productrestriction;                  
         }
		public function getGridUrl()
		{
				return $this->getUrl('*/*/grid', array('_current'=>true));
		} 
     
     
    }