<?php
class Sz_Vendor_Block_Feedback extends Mage_Core_Block_Template
{	
	public function __construct(){		
		parent::__construct();	
		//echo "<pre>";
    	$userId=$this->getProfileDetail()->getMageuserid();
		$collection = Mage::getModel('vendor/feedback')->getCollection()
															   ->addFieldToFilter('status',array('neq'=>0))
															   ->addFieldToFilter('proownerid',array('eq'=>$userId));
		$this->setCollection($collection);
	}
	
	public function _prepareLayout()
    {
		parent::_prepareLayout(); 
		$pager = $this->getLayout()->createBlock('page/html_pager', 'custom.pager');
        $pager->setAvailableLimit(array(5=>5,10=>10,20=>20,'all'=>'all'));
        $pager->setCollection($this->getCollection());
		$temp=explode('/feedback',Mage::helper('core/url')->getCurrentUrl());
		$temp=explode('/',$temp[1]);
		$temp1 = explode('?', $temp[1]);
		$this->getLayout()->getBlock('head')->setTitle($temp1[0].'\'s Feedback');
        $this->setChild('pager', $pager);
        $this->getCollection()->load();
        return $this;
    }
	 public function getPagerHtml() {
        return $this->getChildHtml('pager');
    }
    public function getCustomerpartner(){ 
        if (!$this->hasData('vendor')) {
            $this->setData('vendor', Mage::registry('vendor'));
        }
		$id=$_GET["id"];
		return $id;   
    }
	
	public function getProfileDetail(){
		$temp=explode('/feedback',Mage::helper('core/url')->getCurrentUrl());
		$temp=explode('/',$temp[1]);
		//echo $temp[1]; die();
		if($temp[1]!=''){
			$temp1 = explode('?', $temp[1]);
			$data=Mage::getModel('vendor/userprofile')->getCollection()
						->addFieldToFilter('profileurl',array('eq'=>$temp1[0]));
			foreach($data as $vendor){ return $vendor;}
		}
	}
	public function getFeed(){
		$temp=explode('/feedback',Mage::helper('core/url')->getCurrentUrl());
		$temp=explode('/',$temp[1]);
		if($temp[1]!=''){
			$temp1 = explode('?', $temp[1]);
			$data=Mage::getModel('vendor/userprofile')->getCollection()
						->addFieldToFilter('profileurl',array('eq'=>$temp1[0]));
			foreach($data as $vendor){ $id=$vendor->getMageuserid();}
		}
		return Mage::getModel('vendor/feedback')->getTotal($id);
	}
}