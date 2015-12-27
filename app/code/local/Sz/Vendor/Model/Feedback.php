<?php

class Sz_Vendor_Model_Feedback extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('vendor/feedback');
    }
	public function getTotal($partnerId){
		$data=array();
		if($partnerId > 0){
			$collection = Mage::getModel('vendor/feedback')->getCollection();
			$collection->addFieldToFilter('proownerid',array('eq'=>$partnerId));
			$collection->addFieldToFilter('status',array('neq'=>0));
			$price=0;
			$value=0;
			$quality=0;
			$totalfeed=0;
			foreach($collection as $record) {
				$price+=$record->getfeedprice();
				$value+=$record->getfeedvalue();
				$quality+=$record->getfeedquality();
			}
			if(count($collection)!=0)
			{
			$totalfeed=ceil(($price+$value+$quality)/(3*count($collection)));
		    }
		    
			$data=array('price'=>$price/count($collection),'value'=>$value/count($collection),'quality'=>$quality/count($collection),'totalfeed'=>$totalfeed, 'feedcount'=>count($collection));
			return $data;
		}
	}
	public function saveFeedbackdetail($wholedata)
	{	
		$customerid=Mage::getSingleton('customer/session')->getCustomerId();
		$mail = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
		$wholedata['userid']=$customerid;
		$wholedata['useremail']=$mail; 
		$wholedata['createdat']=date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
		$feedbackcount = 0;
		$collectionfeed=Mage::getModel('vendor/feedbackcount')->getCollection()
							->addFieldToFilter('buyerid',array('eq'=>$customerid))
							->addFieldToFilter('vendorid',array('eq'=>$wholedata['proownerid']));
		foreach ($collectionfeed as $value) {
			$feedcountid = $value->getFeedcountid();
			$ordercount = $value->getOrdercount();
			$feedbackcount = $value->getFeedbackcount();
			$value->setFeedbackcount($feedbackcount+1);
			$value->save();
		}
		$collection=Mage::getModel('vendor/feedback');
		$collection->setData($wholedata);
		$collection->save();
		return 0;
	}
}
