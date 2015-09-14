<?php
class Sz_Vendor_Block_Adminhtml_Uploader_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    public function __construct(){
        parent::__construct();
        $this->setId('vendorGrid');
        $this->setUseAjax(true);
        $this->setDefaultDir('ASC');
        $this->setDefaultSort('id');
        $this->setSaveParametersInSession(true);
        $this->_emptyText = Mage::helper('vendor')->__('No Records Found.');
    }

    protected function _prepareCollection(){
        $collection = Mage::getModel('vendor/uploader')->getCollection();
        $vendorTable =  Mage::getModel('vendor/uploader')->getResource()->getTable(
            'vendor/userprofile'
        );
        $collection->getSelect()->joinInner(
            array('vd' => $vendorTable),
            'vd.mageuserid = main_table.vendor_id',
            array('')
        );

        $customerModel = Mage::getModel('customer/customer');
        $prefix = Mage::getConfig()->getTablePrefix();
        $eavAttribute = new Mage_Eav_Model_Mysql4_Entity_Attribute();
        $fnameid = Mage::getModel("eav/entity_attribute")->loadByCode("1", "firstname")->getAttributeId();
        $lnameid = Mage::getModel("eav/entity_attribute")->loadByCode("1", "lastname")->getAttributeId();
        $collection->getSelect()
                ->join(array("ce1" => $prefix."customer_entity_varchar"),"ce1.entity_id = main_table.vendor_id",array("fname" => "value"))->where("ce1.attribute_id = ".$fnameid)
                ->join(array("ce2" => $prefix."customer_entity_varchar"),"ce2.entity_id = main_table.vendor_id",array("lname" => "value"))->where("ce2.attribute_id = ".$lnameid)
                ->columns(new Zend_Db_Expr("CONCAT(`ce1`.`value`, ' ',`ce2`.`value`) AS fullname"));
        $collection->addFilterToMap("fullname","`ce1`.`value`");

        $this->setCollection($collection);
        parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('file_id', array(
            'header'    => Mage::helper('vendor')->__('ID'),
            'width'     => '50px',
            'index'     => 'id',
            'type'  => 'number',
            'filter_index' => 'main_table.id'
        ));
       
        $this->addColumn('customer_name', array(
            'header'    => Mage::helper('vendor')->__('Vendor Name'),
            'index'     => 'fullname',
            'type'  => 'text',
        ));

        $this->addColumn('attr_set', array(
            'header'    => Mage::helper('vendor')->__('Attribute Set'),
            'index'     => 'attribute_set',
            'type'  => 'options',
            'options'   =>$this->getAllowedSets()
        ));

         $this->addColumn('file_name', array(
             'header'    => Mage::helper('vendor')->__('File'),
             'index'     => 'file_name',
             'type'  => 'text',
        ));
        $this->addColumn('status', array(
            'header'    => Mage::helper('vendor')->__('Status'),
            'index'     => 'status',
            'type'      => 'options',
            'options'   => array(0 => 'PENDING', 1=> 'WORKING', 2=>'COMPLETE', 3=>'ERROR'),
            'frame_callback' => array($this, 'decorateStatus'),
            "align"     => "center"
            // "sortable"  => false
        ));
        return parent::_prepareColumns();
    }
    public function getAllowedSets(){
        $entityTypeId = Mage::getModel('eav/entity')
            ->setType('catalog_product')
            ->getTypeId();
        $data=array();
        $allowed=explode(',',Mage::getStoreConfig('vendor/vendor_options/attributesetid'));
        $attributeSetCollection = Mage::getModel('eav/entity_attribute_set')
            ->getCollection()
            ->addFieldToFilter('attribute_set_id',array('in'=>$allowed))
            ->setEntityTypeFilter($entityTypeId);
        foreach($attributeSetCollection as $_attributeSet){
            $data[$_attributeSet->getData('attribute_set_id')] = $_attributeSet->getData('attribute_set_name');
        }
        return $data;
    }
    public function decorateStatus($value, $row, $column, $isExport)
    {
        $class = '';
        switch ($row->getStatus()) {
            case Sz_Vendor_Model_Uploader::PENDING :
                $class = 'grid-severity-notice';
                break;
            case Sz_Vendor_Model_Uploader::WORKING :
                $class = 'grid-severity-major';
                break;
            case Sz_Vendor_Model_Uploader::COMPLETE :
                $class = 'grid-severity-notice';
                break;
            case Sz_Vendor_Model_Uploader::ERROR:
                $class = 'grid-severity-critical';
                break;
        }
        return '<span class="'.$class.'"><span>'.$value.'</span></span>';
    }

    protected function _prepareMassaction()  {
        $this->setMassactionIdField('main_table.mageproductid');
        $this->getMassactionBlock()->setFormFieldName('file_id');
        $this->getMassactionBlock()->addItem('process', array(
           'label'    => Mage::helper('vendor')->__('Process Files'),
           'url'      => $this->getUrl('vendor/adminhtml_products/process')
        ));
        return $this;
    }

    public function getGridUrl(){
        return $this->getUrl("*/*/processcsvgrid",array("_current"=>true));
    }


}