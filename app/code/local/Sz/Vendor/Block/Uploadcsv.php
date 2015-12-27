<?php
class Sz_Vendor_Block_Uploadcsv extends Mage_Customer_Block_Account_Dashboard
{
	protected $_fileCollection = null;
	public function __construct(){		
		parent::__construct();
        if (is_null($this->_fileCollection)) {
            $userId=Mage::getSingleton('customer/session')->getCustomer()->getId();
            $collection = Mage::getModel('vendor/uploader')->getCollection()->addFieldToFilter('vendor_id',array('eq'=>$userId));
            $this->_fileCollection = $collection;
        }

		$fileName=$this->getRequest()->getParam('s')!=""?$this->getRequest()->getParam('s'):"";
        $this->_fileCollection->addFieldToFilter('file_name',array('like'=>"%".$fileName."%"));
		$filter_prostatus=$this->getRequest()->getParam('prostatus')!=""?$this->getRequest()->getParam('prostatus'):"";
        $filter_file_type=$this->getRequest()->getParam('file_type')?$this->getRequest()->getParam('file_type'):"";
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
        if ($filter_prostatus == 4) {
            $filter_prostatus = 0;
        }
        $this->_fileCollection->addFieldToFilter('status',array('like'=>"%".$filter_prostatus."%"))
						   ->addFieldToFilter('created_at', array('datetime' => true,'from' => $from,'to' =>  $to))
						   ->setOrder('created_at','DESC');
        if ($filter_file_type) {
            $this->_fileCollection->addFieldToFilter('file_type',array('eq'=>$filter_file_type));
        }
		$this->setCollection($this->_fileCollection);
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
