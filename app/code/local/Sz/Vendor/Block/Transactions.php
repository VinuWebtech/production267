<?php
class Sz_Vendor_Block_Transactions extends Mage_Core_Block_Template
{
	protected $_productsCollection = null;
	public function __construct(){		
		parent::__construct();	
    	$id = Mage::getSingleton('customer/session')->getId();
        $transid=$this->getRequest()->getParam('transid')!=""?$this->getRequest()->getParam('transid'):"";
        $filter_data_frm=$this->getRequest()->getParam('from_date')!=""?$this->getRequest()->getParam('from_date'):"";
        $filter_data_to=$this->getRequest()->getParam('to_date')!=""?$this->getRequest()->getParam('to_date'):"";
        if($filter_data_to){
            $todate = date_create($filter_data_to);
            $to = date_format($todate, 'Y-m-d 23:59:59');
        }
        if($filter_data_frm){
            $fromdate = date_create($filter_data_frm);
           $from = date_format($fromdate, 'Y-m-d H:i:s');
        }
        $collection = Mage::getModel('vendor/vendortransaction')->getCollection();
        $collection->addFieldToFilter('vendorid',array('eq'=>$id));
        if($transid){
            $collection->addFieldToFilter('transactionid', array('eq' => $transid));
        }
        if($from || $to){
            $collection->addFieldToFilter('created_at', array('datetime' => true,'from' => $from,'to' =>  $to));
        }
        $collection->setOrder('transid');
		$this->setCollection($collection);
	}
	protected function _prepareLayout() {
        parent::_prepareLayout(); 
        $pager = $this->getLayout()->createBlock('page/html_pager', 'custom.pager');
        $pager->setAvailableLimit(array(5=>5,10=>10,20=>20,'all'=>'all'));
        $pager->setCollection($this->getCollection());
        $this->setChild('pager', $pager);
        $this->getCollection()->load();
        return $this;
    } 
	
    public function getPagerHtml() {
        return $this->getChildHtml('pager');
    }
    public function getremaintotal(){
        $id = Mage::getSingleton('customer/session')->getId();
        $collection = Mage::getModel('vendor/saleperpartner')->getCollection();
        $collection->addFieldToFilter('mageuserid',array('eq'=>$id));
        $total=0;
        foreach($collection as $key){ 
            $total=$key->getAmountremain();
        }
        if($total<0){
            $total=0;
        }
        return $total;
    }
}
