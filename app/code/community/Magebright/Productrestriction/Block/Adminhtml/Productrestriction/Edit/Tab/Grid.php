<?php

    class Magebright_Productrestriction_Block_Adminhtml_Productrestriction_Edit_Tab_Grid extends Mage_Adminhtml_Block_Widget_Grid
    {
        public function __construct()
        {
            parent::__construct();
            $this->setId('product');
            $this->setUseAjax(true); // Using ajax grid is important
            $this->setDefaultSort('entity_id');
            $this->setDefaultFilter(array('in_products'=>1));
            $this->setSaveParametersInSession(false);  //Dont save paramters in session or else it creates problems


            /* store attribute option in registry */

        }

        protected function _getStore()
        {
            $storeId = (int) $this->getRequest()->getParam('store', 0);
            return Mage::app()->getStore($storeId);
        }
        /*
        prepare colleciton for attribute product 
        */
        protected function _prepareCollection()
        {	
		$store = $this->_getStore();
            $collection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('price');
            $this->setCollection($collection); 	
            return parent::_prepareCollection();   
        }

        /*
        reset filter feature
        */
        protected function _addColumnFilterToCollection($column)
        {
            // Set custom filter for in product flag
            if ($column->getId() == 'in_products') {
                $ids = $this->_getSelectedProducts();
                if (empty($ids)) {
                    $ids = 0;
                }
                if ($column->getFilter()->getValue()) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$ids));
                } else {
                    if($ids) {
                        $this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$ids));
                    }
                }
            } else { 
                parent::_addColumnFilterToCollection($column);
            }

            return $this;
        }
        /*
        grid preparatiuon
        */
        protected function _prepareColumns()
        {
            /* set checkbox column to get selected attribute option products */
            $this->addColumn('in_products', array(
                    'header_css_class'  => 'a-center',
                    'type'              => 'checkbox',
                    'name'              => 'product',
                    'values'            => $this->_getSelectedProducts(),
                    'align'             => 'center',
                    'index'             => 'entity_id'
                ));

            $this->addColumn('entity_id',
                array(
                    'header'=> Mage::helper('catalog')->__('ID'),
                    'width' => '50px',
                    'type'  => 'number',
                    'index' => 'entity_id',
                ));
            $this->addColumn('name',
                array(
                    'header'=> Mage::helper('catalog')->__('Name'),
                    'index' => 'name',
                ));


            $this->addColumn('sku',
                array(
                    'header'=> Mage::helper('catalog')->__('SKU'),
                    'width' => '80px',
                    'index' => 'sku',
                ));

            $store = $this->_getStore();
            $this->addColumn('price',
                array(
                    'header'=> Mage::helper('catalog')->__('Price'),
                    'type'  => 'price',
                    'currency_code' => $store->getBaseCurrency()->getCode(),
                    'index' => 'price',
                ));

            $this->addColumn('position', array(
                    'header'            => Mage::helper('catalog')->__('Position'),
                    'name'              => 'position',
                    'width'             => 0,
                    'type'              => 'number',
                    'validate_class'    => 'validate-number',
                    'index'             => 'position',
					'filter'            => false,
                    'sortable'          => false,
                    'editable'          => true,
                    'edit_only'         => true,
                    'column_css_class'=>'no-display',
                    'header_css_class'=>'no-display'
                ));   

            return parent::_prepareColumns();
        }


        public function getProductrestriction()
        {
            return Mage::registry('zipcode');
        }
        /**
        * get selected products
        *
        * @return array|mixed
        */
        protected function _getSelectedProducts()
        {
            $products = $this->getRequest()->getPost('selected_products');
            $selectedProducts = array();
            if (is_null($products))
            {
                $zipcode_product =Mage::getModel('productrestriction/productrestriction')->load(Mage::app()->getRequest()->getParam('id'));

                //$product_collection=explode(',',$zipcode_product->getProductId());
                $product_collection =Mage::getModel('productrestriction/zipcodeproduct')->getCollection()->addFieldToFilter('pin_code',array('eq'=>$zipcode_product->getPinCode()));


                foreach($product_collection as $prod_id)
                {
                      $selectedProducts[]=$prod_id->getProductId();
                }

                return $selectedProducts;
            }

            return $products;
        }

        /**
        * get selected products
        *
        * @return array
        */
        public function getSelectedProducts()
        { 
        	$selectedProducts = array();           
           $zipcode_product =Mage::getModel('productrestriction/productrestriction')->load(Mage::app()->getRequest()->getParam('id'));

                $product_collection =Mage::getModel('productrestriction/zipcodeproduct')->getCollection()->addFieldToFilter('pin_code',array('eq'=>$zipcode_product->getPinCode()));

				$i=0;	
                foreach($product_collection as $prod_id)
                {
					   $i++;
					   $pid=$prod_id->getProductId();
                $selectedProducts[$pid]= array('position' => $i);
                }
            return $selectedProducts;

        }
        /*
        grid url for ajax request
        */
        public function getGridUrl()
        {
            return $this->_getData('grid_url') ? $this->_getData('grid_url') : $this->getUrl('*/*/productgrid', array('_current'=>true));
        }



}
