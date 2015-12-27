<?php
class Sz_Vendor_Block_Vendor extends Mage_Customer_Block_Account_Dashboard
{
	protected $_productsCollection = null;
	public function __construct(){		
		parent::__construct();	
    	$userId=Mage::getSingleton('customer/session')->getCustomer()->getId();
		$collection = Mage::getModel('vendor/product')->getCollection()->addFieldToFilter('userid',array('eq'=>$userId));
		$products=array();
		foreach($collection as $data){
			array_push($products,$data->getMageproductid());
		}
		$filter=$this->getRequest()->getParam('s')!=""?$this->getRequest()->getParam('s'):"";
		
		$filter_prostatus=$this->getRequest()->getParam('prostatus')!=""?$this->getRequest()->getParam('prostatus'):"";

		$filter_data_frm=$this->getRequest()->getParam('from_date')!=""?$this->getRequest()->getParam('from_date'):"";
		$filter_data_to=$this->getRequest()->getParam('to_date')!=""?$this->getRequest()->getParam('to_date'):"";
		if($filter_data_to){
			$todate = date_create($filter_data_to);
			$to = date_format($todate, 'Y-m-d H:i:s');
		}
		if($filter_data_frm){
			$fromdate = date_create($filter_data_frm);
			$from = date_format($fromdate, 'Y-m-d H:i:s');
		}

		$collection = Mage::getModel('catalog/product')->getCollection()
						   ->addAttributeToSelect('*')
						   ->addFieldToFilter('name',array('like'=>"%".$filter."%"))
						   ->addFieldToFilter('status',array('like'=>"%".$filter_prostatus."%"))
						   ->addFieldToFilter('created_at', array('datetime' => true,'from' => $from,'to' =>  $to))
						   ->addFieldToFilter('entity_id',array('in'=>$products))
						   ->setOrder('entity_id','AESC');
		$this->setCollection($collection);
	}
	protected function _prepareLayout() {
        parent::_prepareLayout(); 
        $pager = $this->getLayout()->createBlock('page/html_pager', 'custom.pager');
        $grid_per_page_values = explode(",",Mage::getStoreConfig('catalog/frontend/grid_per_page_values'));
        $arr_perpage = array();
        foreach ($grid_per_page_values as $value) {
        	$arr_perpage[$value] = $value;
        }
        $pager->setAvailableLimit($arr_perpage);
        $pager->setCollection($this->getCollection());
        $this->setChild('pager', $pager);
        $this->getCollection()->load();
        return $this;
    } 
	
    public function getPagerHtml() {
        return $this->getChildHtml('pager');
    }
	public function getProduct() {
		$id = $this->getRequest()->getParam('id');
		$products = Mage::getModel('catalog/product')->load($id);
		return $products;
	}
}
