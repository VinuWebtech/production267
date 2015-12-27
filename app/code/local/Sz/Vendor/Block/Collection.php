<?php
class Sz_Vendor_Block_Collection  extends Mage_Catalog_Block_Product_Abstract
{
	//protected $_defaultToolbarBlock = 'catalog/product_list_toolbar';
	public function __construct(){		
		parent::__construct();
		if(array_key_exists('c', $_GET)){	
    	$cate = Mage::getModel('catalog/category')->load($_GET["c"]);
	    }	
    	$partner=$this->getProfileDetail();
        $productname=$this->getRequest()->getParam('name');
		$querydata = Mage::getModel('vendor/product')->getCollection()
				->addFieldToFilter('userid', array('eq' => $partner->getmageuserid()))
				->addFieldToFilter('status', array('neq' => 2))
				->setOrder('mageproductid');
		$rowdata=array();		
		foreach ($querydata as  $value) {
            $stock_item_details = Mage::getModel('cataloginventory/stock_item')->loadByProduct($value->getMageproductid());
            $stock_availability = $stock_item_details->getIsInStock();
            if($stock_availability){
                $rowdata[] = $value->getMageproductid();
            }			
		}
		$collection = Mage::getModel('catalog/product')->getCollection();
		$collection->addAttributeToSelect('*');
		
		if(array_key_exists('c', $_GET)){
			$collection->addCategoryFilter($cate);
		}
        $collection->addAttributeToFilter('entity_id', array('in' => $rowdata));
		if((Mage::helper('core')->isModuleEnabled('Sz_Szsearch')) && ($productname!='')){
            $collection->addFieldToFilter('name', array('like' => '%'.$productname.'%'));   
        }
		$this->setCollection($collection);	
	}

	protected function _prepareLayout() {
        parent::_prepareLayout();		
        $toolbar = $this->getToolbarBlock();
        $collection = $this->getCollection();
		
        if ($orders = $this->getAvailableOrders()) {
           $toolbar->setAvailableOrders($orders);
        }
        if ($sort = $this->getSortBy()) {
            $toolbar->setDefaultOrder($sort);
        }
        if ($dir = $this->getDefaultDirection()) {
            $toolbar->setDefaultDirection($dir);
        }
        $toolbar->setCollection($collection);
 
        $this->setChild('toolbar', $toolbar);
        $this->getCollection()->load();		
		$partner=$this->getProfileDetail();		
		if($partner->getShoptitle()!='')
			$this->getLayout()->getBlock('head')->setTitle($partner->getShoptitle());
		else
			$this->getLayout()->getBlock('head')->setTitle($partner->getProfileurl());
		$this->getLayout()->getBlock('head')->setKeywords($partner->getMetaKeyword());		
		$this->getLayout()->getBlock('head')->setDescription($partner->getMetaDescription());
        return $this;
    }
	
	public function getProfileDetail(){
		$temp=explode('/collection',Mage::helper('core/url')->getCurrentUrl());
		$temp=explode('/',$temp[1]);
		if($temp[1]!=''){
            $temp1 = explode('?', $temp[1]);
			$data=Mage::getModel('vendor/userprofile')->getCollection()
						->addFieldToFilter('profileurl',array('eq'=>$temp1[0]));
			foreach($data as $vendor){ return $vendor;}
		}
	}
	
    public function getDefaultDirection(){
        return 'asc';
    }
    public function getAvailableOrders(){
        return array('price'=>'Price','name'=>'Name');
    }
    public function getSortBy(){
        return 'collection_id';
    }
     public function getToolbarBlock(){
       $block = $this->getLayout()->createBlock('vendor/toolbar', microtime());
        return $block;
    }
    /*public function getToolbarBlock()
    {
       if ($blockName = $this->getToolbarBlockName()) {
            if ($block = $this->getLayout()->getBlock($blockName)) {
                return $block;
            }
        }
        $block = $this->getLayout()->createBlock($this->_defaultToolbarBlock, microtime());
        return $block;
    }*/
    public function getMode()
    {
        return $this->getChild('toolbar')->getCurrentMode();
    }
 
    public function getToolbarHtml()   {
        return $this->getChildHtml('toolbar');
    }
	public function getColumnCount() {
		return 4;
    } 
}
