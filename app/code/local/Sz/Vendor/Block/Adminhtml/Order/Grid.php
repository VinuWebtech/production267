<?php
class Sz_Vendor_Block_Adminhtml_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
    parent::__construct();
    $this->setId('vendorGrid');
    $this->setUseAjax(true);
    
    $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {   
    $customerid=$this->getRequest()->getParam('id');
    $collection = Mage::getModel('vendor/saleslist')->getCollection();
    $collection->addFieldToFilter('mageproownerid',array('eq'=>$customerid));
    $collection->addFieldToFilter('magerealorderid',array('neq'=>0));
    //$collection->setOrder('cpprostatus');
    //$collection->setOrder('paidstatus','ASC');
    $prefix = Mage::getConfig()->getTablePrefix();
    $collection->getSelect()
        ->join(array("ccp" => $prefix."sales_flat_order"),"ccp.entity_id = main_table.mageorderid",array("status" => "status"));
    $this->setCollection($collection);
    parent::_prepareCollection();
    foreach ($collection as $item) {
      $item->view='<a class="wk_vendororderstatus" wk_cpprostatus="'.$item->getCpprostatus().'" href="'.$this->getUrl('adminhtml/sales_order/view/',array('order_id'=>$item->getMageorderid())).'" title="'.Mage::helper('vendor')->__('View Order').'">'.Mage::helper('vendor')->__('View Order').'</a>';
      if(($item->getPaidstatus()==0) && ($item->getCpprostatus()==1)){
        $item->payvendor='<button type="button" class="button wk_payvendor" auto-id="'.$item->getAutoid().'" title="'.Mage::helper('vendor')->__('Pay Vendor').'"><span><span><span>'.Mage::helper('vendor')->__('Pay Vendor').'</span></span></span></button>';
      }
      else if(($item->getPaidstatus()==0) && ($item->getCpprostatus()==0)){
        $item->payvendor=Mage::helper('vendor')->__('Order Pending');
      }else{
        $item->payvendor=Mage::helper('vendor')->__('Already Paid');
      }
    }   
  }

  protected function _prepareColumns(){
    $this->addColumn('mageorderid', array(
      'header'    => Mage::helper('vendor')->__('Order ID'),
      'width'     => '50px',
      'index'     => 'mageorderid',
    ));
    $this->addColumn('magerealorderid', array(
      'header'    => Mage::helper('vendor')->__('Order#'),
      'index'     => 'magerealorderid',
      'width' => '80px',
    ));    
    $this->addColumn('cleared_at', array(
      'header'    => Mage::helper('vendor')->__('Purchased On'),
      'type'      => 'datetime',
      'index'     => 'cleared_at',
      'width' => '100px',
    ));
    $this->addColumn('mageproname', array(
      'header'    => Mage::helper('vendor')->__('Product Name'),
      'index'     => 'mageproname',
    ));
    $this->addColumn('magequantity', array(
      'header'    => Mage::helper('vendor')->__('Product Quantity'),
      'index'     => 'magequantity',
      'width' => '100px',
    ));
    $this->addColumn('mageproprice', array(
      'header'    => Mage::helper('vendor')->__('Product Price'),
      'index'     => 'mageproprice',
      'currency_code' => $this->getcurrency(),
      'type'      => 'currency',
      'column_css_class' => 'wkmageproprice',
    ));
    $this->addColumn('totalamount', array(
      'header'    => Mage::helper('vendor')->__('Total Amount'),
      'index'     => 'totalamount',
      'currency_code' => $this->getcurrency(),
      'type'      => 'currency',
      'column_css_class' => 'wktotalamount',
    ));
    $this->addColumn('totaltax', array(
      'header'    => Mage::helper('vendor')->__('Total Tax'),
      'index'     => 'totaltax',
      'currency_code' => $this->getcurrency(),
      'type'      => 'currency',
      'column_css_class' => 'wktotaltax',
    ));
    $this->addColumn('totalcommision', array(
      'header'    => Mage::helper('vendor')->__('Total Commission'),
      'index'     => 'totalcommision',
      'currency_code' => $this->getcurrency(),
      'type'      => 'currency',
      'column_css_class' => 'wktotalcommision',
    ));
    $this->addColumn('actualparterprocost', array(
      'header'    => Mage::helper('vendor')->__('Total Vendor Amount'),
      'index'     => 'actualparterprocost',
      'currency_code' => $this->getcurrency(),
      'type'      => 'currency',
      'column_css_class' => 'wkactualparterprocost',
    ));
    $this->addColumn('status', array(
      'header'    => Mage::helper('vendor')->__('Status'),
      'index'     => 'status',
      'type'      => 'options',
      'column_css_class' => 'wk_orderstatus',
      'width'     => '70px',
      'options'   => Mage::getSingleton('sales/order_config')->getStatuses(),
    ));
    $this->addColumn('paidstatus', array(
      'header' => Mage::helper('sales')->__('Paid Status'),
      'index' => 'paidstatus',
      'type'  => 'options',
      'width' => '70px',
      'column_css_class' => 'wk_paidstatus',
      'options' => $this->getpaidStatuses(),
    ));
    $this->addColumn('view', array(
      'header'    => Mage::helper('customer')->__('View'),
      'index'     => 'view',
      'type'      => 'text',
      'filter'    => false,
      'sortable'  => false
    ));
    $this->addColumn('payvendor', array(
      'header'    => Mage::helper('vendor')->__('Pay'),
      'index'     => 'payvendor',
      'type'      => 'text',
      'filter'    => false,
      'sortable'  => false
    ));
    return parent::_prepareColumns();
  }

  protected function _prepareMassaction(){
    $this->setMassactionIdField('mageorderid');
    $this->getMassactionBlock()->setFormFieldName('vendororderids');
    $this->getMassactionBlock()->setUseSelectAll(false);
    $this->getMassactionBlock()->setUseUnSelectAll(false);

    $this->getMassactionBlock()->addItem('pay', array(
     'label'    => Mage::helper('customer')->__('Pay'),
     'url'      => $this->getUrl('*/*/masspay', array('vendor_id' => $this->getRequest()->getParam('id'))),
     'confirm' => Mage::helper('tax')->__('Are you want to make this payment?')
    ));
    return $this;
  }
  public function getGridUrl(){
    return $this->getUrl("*/*/grid",array("_current"=>true));
  }
  public function getRowUrl($row) {
    return '#';
  }
  public function getcurrency(){        
    return (string)Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);
  }
  public function getpaidStatuses(){
    return array('0'=>'Pending','1'=>'Paid');
  }
}