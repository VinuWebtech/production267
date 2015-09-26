<?php
class Sz_Vendor_Block_Adminhtml_Products_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    public function __construct(){
        parent::__construct();
        $this->setId('vendorGrid');
        $this->setUseAjax(true);
        $this->setDefaultDir('DESC');
        $this->setDefaultFilter( array('status' => 2));
        $this->setSaveParametersInSession(true);
        $this->_emptyText = Mage::helper('vendor')->__('No Products Found.');
    }

    protected function _prepareCollection(){
        $collection = Mage::getModel('vendor/product')->getCollection();
        $productTable = Mage::getResourceModel('catalog/product')->getEntityTable();
        $catalogInventoryStockItemTable = Mage::getSingleton('core/resource')->getTableName(
            'cataloginventory/stock_item'
        );
        $productNameAttribute = Mage::getResourceModel('catalog/product')->getAttribute(
            'name'
        );
        $productWeightAttribute = Mage::getResourceModel('catalog/product')->getAttribute(
            'weight'
        );
        $productPriceAttribute = Mage::getResourceModel('catalog/product')->getAttribute(
            'price'
        );
        $collection->getSelect()->joinInner(
            array('pt' => $productTable),
            'pt.entity_id = main_table.mageproductid',
            array('pt.sku as sku','pt.created_at as created_at')
        );
        $collection->getSelect()->joinInner(
            array('ct' => $catalogInventoryStockItemTable),
            'ct.product_id = main_table.mageproductid',
            array('ct.qty as qty', 'ct.is_in_stock as is_in_stock')
        );
        $collection->getSelect()->joinInner(
            array('pn' => $productTable.'_'.$productNameAttribute->getBackendType()),
            'pn.entity_id = main_table.mageproductid AND
            pn.attribute_id = '.$productNameAttribute->getId(),
            array('pn.value as product_name')
        );

        $collection->getSelect()->joinLeft(
            array('pw' => $productTable.'_'.$productWeightAttribute->getBackendType()),
            'pw.entity_id = main_table.mageproductid AND
            pw.attribute_id = '.$productWeightAttribute->getId(),
            array('pw.value as weight')
        );
        $collection->getSelect()->joinLeft(
            array('pp' => $productTable.'_'.$productPriceAttribute->getBackendType()),
            'pp.entity_id = main_table.mageproductid AND
            pp.attribute_id = '.$productPriceAttribute->getId(),
            array('pp.value as price')
        );
        $vendorId = $this->getRequest()->getParam('vendor_id', 0);
        if(($this->getRequest()->getParam('unapp')==1) || $vendorId){
           //$collection->addFieldToFilter('status', array('neq' => '1'));

        }
        if ($vendorId) {
            $collection->addFieldToFilter('userid', array('eq' => $vendorId));
        }

        $this->setCollection($collection);
        parent::_prepareCollection();
        foreach ($this->getCollection() as $item) {

            if(!(is_null($item->getmageproductid())) && $item->getmageproductid() != 0){
                 if($item->getstatus() == 1){
                    $item->action = sprintf('<a href="%s" title="View product">Approved</a>',
                                             $this->getUrl('adminhtml/catalog_product/edit/id/' . $item->getmageproductid(),
                                                 array('vendor_id'=>$vendorId))
                                            );
                }
                else{
                    $item->action = sprintf('<a href="%s" title="Click to Approve" onclick="return confirm(\'You sure?\')">Unapproved</a>',$this->getUrl(
                        'vendor/adminhtml_products/approve/id/' . $item->getmageproductid(),
                        array('vendor_id'=>$vendorId)
                    ));
                }

            }
        }
    }

    protected function _prepareColumns() {
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('vendor')->__('ID'),
            'width'     => '50px',
            'index'     => 'mageproductid',
            'type'  => 'number',
            'filter_index' => 'main_table.mageproductid'
        ));

        $this->addColumn('product_name', array(
            'header'    => Mage::helper('vendor')->__('Product Name'),
            'index'     => 'product_name',
            'type'  => 'string',
        ));
        $this->addColumn('sku', array(
            'header'    => Mage::helper('vendor')->__('Product SKU'),
            'index'     => 'sku',
            'type'  => 'string',
        ));
         $this->addColumn('price', array(
            'header'    => Mage::helper('vendor')->__('Price'),
            'index'     => 'price',
            'currency_code' => $this->getcurrency(),
            'type'  => 'price',
        ));
        $this->addColumn('stock', array(
            'header'    => Mage::helper('vendor')->__('Stock'),
            'index'     => 'qty',
            'type'  => 'number',
            "filter"    => false,
            "sortable"  => false
        ));
        $this->addColumn('weight', array(
            'header'    => Mage::helper('vendor')->__('Weight'),
            'index'     => 'weight',
            'type'  => 'number',
            "filter"    => false,
            "sortable"  => false
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('vendor')->__('Product Status'),
            'index'     => 'status',
            'type'      => 'options',
            'options'   => array(1 => 'Approved', 2=> 'Unapproved'),
        ));
        $this->addColumn('created_at', array(
            'header'    => Mage::helper('vendor')->__('Created'),
            'index'     => 'created_at',
            'type'  => 'datetime',
        ));
        $this->addColumn('action', array(
            'header'    => Mage::helper('vendor')->__('Action'),
            'index'     => 'action',
            "type"      => "text",
            "align"     => "center",
            "filter"    => false,
            // "sortable"  => false
        ));
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()  {
        $this->setMassactionIdField('main_table.index_id');
        $this->getMassactionBlock()->setFormFieldName('vendorproduct');
        $vendorId = $this->getRequest()->getParam('vendor_id', 0);
        $this->getMassactionBlock()->addItem('approve', array(
           'label'    => Mage::helper('vendor')->__('Approve'),
           'url'      => $this->getUrl('vendor/adminhtml_products/massapprove', array('vendor_id'=>$vendorId))
        ));
        return $this;
    }

    public function getGridUrl(){
        return $this->getUrl("*/*/grid",array("_current"=>true));
    }

    public function getcurrency(){        
        return (string)Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);
    }
}