<?php

class Sz_Vendor_Block_Products extends Mage_Core_Block_Template
{
    protected $_productsCollection = null;
    public function __construct(){      
        parent::__construct();
        $ids = array();
        $ordeids = array();

        $filter_orderstatus=$this->getRequest()->getParam('orderstatus')!=""?$this->getRequest()->getParam('orderstatus'):"";        

        $userid = Mage::getSingleton('customer/session')->getId();
        $collection_orders = Mage::getModel('vendor/saleslist')->getCollection()
                                ->addFieldToFilter('mageproownerid',array('eq'=>$userid))
                                ->addFieldToSelect('mageorderid')
                                ->distinct(true);
        foreach ($collection_orders as $collection_order) {            
            array_push($ordeids, $collection_order->getMageorderid());
        }

        $filter_orders = Mage::getModel('sales/order')->getCollection();
        if($filter_orderstatus){
            $filter_orders->addFieldToFilter('status',array('eq'=>$filter_orderstatus));
        }                            
        $filter_orders->addFieldToFilter('entity_id',array('in'=>$ordeids));
       
        foreach ($filter_orders as $filter_order) {
            $collection_ids = Mage::getModel('vendor/saleslist')->getCollection()
                            ->addFieldToFilter('mageorderid',array('eq'=>$filter_order->getId()))
                            ->setOrder('autoid','DESC')
                            ->setPageSize(1);
            foreach ($collection_ids as $collection_id) {
                $autoid = $collection_id->getAutoid();
            }
            array_push($ids, $autoid);
        }

        
        $filter_orderid=$this->getRequest()->getParam('s')!=""?$this->getRequest()->getParam('s'):"";
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

        $collection = Mage::getModel('vendor/saleslist')->getCollection();
        $collection->addFieldToFilter('autoid',array('in'=>$ids))
                   ->addFieldToFilter('cleared_at', array('datetime' => true,'from' => $from,'to' =>  $to));
        if($filter_orderid){
            $collection->addFieldToFilter('magerealorderid', array('in' => $filter_orderid));
        }                   
		$collection->setOrder('autoid','AESC');
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
}
