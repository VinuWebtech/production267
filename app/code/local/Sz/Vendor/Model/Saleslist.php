<?php

class Sz_Vendor_Model_Saleslist extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('vendor/saleslist');
    }
	
	public function getProductSalesDetailById($productId){
		$data = array();
        if($productId > 0){
           $collection = Mage::getModel('vendor/saleslist')->getCollection();
           $collection->addFieldToFilter('mageproid',array('eq'=>$productId));
			$i=0;
			foreach ($collection as $record) {
				$data[$i]=array(
							'magequantity'=>$record->getmagequantity(),
							'actualparterprocost'=>$record->getactualparterprocost()
						);
				$i++;
			}
			return $data;
		}
	}
	
	public function getCommsionCalculation($order){
        try {
            $percent = Mage::getStoreConfig('vendor/vendor_options/percent');
            $lastOrderId=$order->getId();
            $ordercollection=Mage::getModel('vendor/saleslist')->getCollection()
                                            ->addFieldToFilter('mageorderid',array('eq'=>$lastOrderId))
                                            ->addFieldToFilter('cpprostatus',array('eq'=>0));
            foreach($ordercollection as $item){
                $actparterprocost = $item->getActualparterprocost();
                $totalamount = $item->getTotalamount();
                $vendor_id = $item->getMageproownerid();
                $salesCommissionSoFar = Mage::getModel('vendor/saleperpartner')->loadByVendorId($vendor_id);
                if($salesCommissionSoFar instanceof Sz_Vendor_Model_Saleperpartner){
                    $totalsale=$salesCommissionSoFar->getTotalsale()+$totalamount;
                    $totalremain=$salesCommissionSoFar->getAmountremain()+$actparterprocost;
                    $salesCommissionSoFar->setTotalsale($totalsale);
                    $salesCommissionSoFar->setAmountremain($totalremain);
                    $salesCommissionSoFar->save();
                } else{
                    $percent = Mage::getStoreConfig('vendor/vendor_options/percent');
                    $collectionf = Mage::getModel('vendor/saleperpartner');
                    $collectionf->setMageuserid($vendor_id);
                    $collectionf->setTotalsale($totalamount);
                    $collectionf->setAmountremain($actparterprocost);
                    $collectionf->setCommision($percent);
                    $collectionf->save();
                }
                Mage::getModel('vendor/saleslist')->load($item->getAutoid())->setCpprostatus(1)->save();
            }
        } catch (exception $e) {
            Mage::log($e, null, 'vendor_errors.log', true);
        }
	}

	public function payvendorpayment($order,$vendorid,$trid){
		$lastOrderId=$order->getId();		
		$actparterprocost = 0;
		$totalamount = 0;
		$collection = Mage::getModel('vendor/saleslist')->getCollection()
								->addFieldToFilter('cpprostatus',array('eq'=>1))
								->addFieldToFilter('paidstatus',array('eq'=>0))
								->addFieldToFilter('mageproownerid',$vendorid)
								->addFieldToFilter('mageorderid',array('eq'=>$lastOrderId));
		foreach ($collection as $row) {
			$actparterprocost = $actparterprocost + $row->getActualparterprocost();
			$totalamount = $totalamount + $row->getTotalamount();
			$vendor_id = $row->getMageproownerid();
		}
		if($actparterprocost){		
			$collectionverifyread = Mage::getModel('vendor/saleperpartner')->getCollection();
			$collectionverifyread->addFieldToFilter('mageuserid',array('eq'=>$vendor_id));
			if(count($collectionverifyread)>=1){
				foreach($collectionverifyread as $verifyrow){
					if($verifyrow->getAmountremain() >= $actparterprocost){
						$totalremain=$verifyrow->getAmountremain()-$actparterprocost;
					}
					else{
						$totalremain=0;
					}
					$verifyrow->setAmountremain($totalremain);
					$verifyrow->save();
					$totalremain;
					$amountpaid=$verifyrow->getAmountrecived();
					$totalrecived=$actparterprocost+$amountpaid;
					$verifyrow->setAmountpaid($actparterprocost);
					$verifyrow->setAmountrecived($totalrecived);
					$verifyrow->setAmountremain($totalremain);
					$verifyrow->save();
				}
			}
			else{
				$percent = Mage::getStoreConfig('vendor/vendor_options/percent');			
				$collectionf=Mage::getModel('vendor/saleperpartner');
				$collectionf->setMageuserid($vendor_id);
				$collectionf->setTotalsale($totalamount);
				$collectionf->setAmountpaid($actparterprocost);
				$collectionf->setAmountrecived($actparterprocost);
				$collectionf->setAmountremain(0);
				$collectionf->setCommision($percent);
				$collectionf->save();						
			}

			$unique_id = $this->checktransid();
			if($unique_id!=''){
				$vendor_trans = Mage::getModel('vendor/vendortransaction')->getCollection()
	                    ->addFieldToFilter('transactionid',array('eq'=>mysqli_real_escape_string($unique_id)));            
	            if(count($vendor_trans)){
					foreach ($vendor_trans as $value) {
						$id =$value->getId();
						if($id){
							Mage::getModel('vendor/vendortransaction')->load($id)->delete();
						}
			    	}
				}
				if($order->getPayment()){
		  			$paymentCode = $order->getPayment()->getMethod();
		  			$payment_type=Mage::getStoreConfig('payment/'.$paymentCode.'/title');
		  		}else{
		  			$payment_type='Manual';
		  		}
				$currdate = date('Y-m-d H:i:s');
				$vendor_trans = Mage::getModel('vendor/vendortransaction');
				$vendor_trans->setTransactionid($unique_id);
				$vendor_trans->setOnlinetrid($trid);
				$vendor_trans->setTransactionamount($actparterprocost);
				$vendor_trans->setType('Online');
				$vendor_trans->setMethod($payment_type);
				$vendor_trans->setVendorid($vendor_id);
				$vendor_trans->setCustomnote('None');
				$vendor_trans->setCreatedAt($currdate);
				$transid = $vendor_trans->save()->getTransid();
			}
			
			$collection = Mage::getModel('vendor/saleslist')->getCollection()
							->addFieldToFilter('cpprostatus',array('eq'=>1))
							->addFieldToFilter('paidstatus',array('eq'=>0))
							->addFieldToFilter('mageorderid',array('eq'=>$lastOrderId))
							->addFieldToFilter('mageproownerid',$vendorid);
			foreach ($collection as $row) {
				$row->setPaidstatus(1);
				$row->setTransid($transid)->save();
			}
		}
		
		Mage::getSingleton('core/session')->unsetData('onlinevendortrids');
	}

	public function randString($length, $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')
	{
	    $str = 'tr-';
	    $count = strlen($charset);
	    while ($length--) {
	        $str .= $charset[mt_rand(0, $count-1)];
	    }

	    return $str;
	}
	
	public function checktransid(){
		$unique_id=$this->randString(11);
		$collection = Mage::getModel('vendor/vendortransaction')->getCollection()
                    ->addFieldToFilter('transactionid',array('eq'=>mysqli_real_escape_string($unique_id)));
        $i=0;
    	foreach ($collection as $value) {
    			$i++;
    	}   
    	if($i!=0){
            $this->checktransid();
        }else{
        	return $unique_id;
        }		
	}
	
	public function getProductSalesCalculation($order){
		$percent = Mage::getStoreConfig('vendor/vendor_options/percent');
		$lastOrderId=$order->getId();
		
		foreach ($order->getAllItems() as $item){
			$item_data = $item->getData();
			$attrselection = unserialize($item_data['product_options']);
			$bundle_selection_attributes = unserialize($attrselection['bundle_selection_attributes']);
			if(!$bundle_selection_attributes['option_id']){			
				$temp=$item->getProductOptions();
			 	if (array_key_exists('vendor_id', $temp['info_buyRequest'])) {
					$vendor_id= $temp['info_buyRequest']['vendor_id'];
				}
				else {
					$vendor_id='';
				}	
				$currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
				$baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();		
				$allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies(); 
				$rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
				$price=$item->getPrice()/$rates[$currentCurrencyCode];
				if($vendor_id==''){
					$collection_product = Mage::getModel('vendor/product')->getCollection();
					$collection_product->addFieldToFilter('mageproductid',array('eq'=>$item->getProductId()));
					foreach($collection_product as $selid){
						$vendor_id=$selid->getuserid();
					}
				}
			  	if($vendor_id==''){$vendor_id=0;}		
				$collection1 = Mage::getModel('vendor/saleperpartner')->getCollection();
				$collection1->addFieldToFilter('mageuserid',array('eq'=>$vendor_id));
				$taxamount=$item_data['tax_amount'];
				$qty=$item->getQtyOrdered();
				$totalamount=$qty*$price;
				
				if(count($collection1)!=0){
					foreach($collection1 as $rowdatasale) {
						$commision=($totalamount*$rowdatasale->getcommision())/100;
					}
			 	}
				else{
					$commision=($totalamount*$percent)/100;
				}	
						
				$wholedata['id'] = $item->getProductId();
				Mage::dispatchEvent('mp_advance_commission', $wholedata);
				$advancecommission = Mage::getSingleton('core/session')->getData('commission');
				if($advancecommission!=''){
					$percent=$advancecommission;
					$commType = Mage::getStoreConfig('mpadvancecommission/mpadvancecommission_options/commissiontype');
					if($commType=='fixed')
					{
						$commision=$percent;
					}
					else
					{
						$commision=($totalamount*$advancecommission)/100;
					}	  
					if($commision>$totalamount){ $commission= $totalamount*(Mage::getStoreConfig('vendor/vendor_options/percent'))/100; }
				}			
						
				$actparterprocost=$totalamount-$commision;
				$collectionsave=Mage::getModel('vendor/saleslist');
				$collectionsave->setmageproid($item->getProductId());
				$collectionsave->setmageorderid($lastOrderId);
				$collectionsave->setmagerealorderid($order->getIncrementId());
				$collectionsave->setmagequantity($qty);
				$collectionsave->setmageproownerid($vendor_id);
				$collectionsave->setcpprostatus(0);
				$collectionsave->setmagebuyerid(Mage::getSingleton('customer/session')->getCustomer()->getId());
				$collectionsave->setmageproprice($price);
				$collectionsave->setmageproname($item->getName());
				if($totalamount!=0){
				$collectionsave->settotalamount($totalamount);
				}
				else{
				$collectionsave->settotalamount($price);
				}
				$collectionsave->setTotaltax($taxamount);
				if(Mage::getStoreConfig('vendor/vendor_options/taxmanage')){
					$actparterprocost=$actparterprocost+$taxamount;
				}				
				$collectionsave->settotalcommision($commision);
				$collectionsave->setactualparterprocost($actparterprocost);
				$collectionsave->setcleared_at(date('Y-m-d H:i:s'));
				$collectionsave->save();
				$qty='';
			}
		}
	}
	
	public function getSalesdetail($mageproid){
		$data = array(
				'quantitysoldconfirmed'=>0,
				'quantitysoldpending'=>0,
				'amountearned'=>0,
				'clearedat'=>0,
				'quantitysold'=>0,
				);
		$sum=0;
		$arr=array();
		$quantity = Mage::getModel('vendor/saleslist')->getCollection()
						->addFieldToFilter('mageproid',array('eq'=>$mageproid));

	   foreach($quantity as $rec){
		 $status=$rec->getCpprostatus();
		 $data['quantitysold']=$data['quantitysold']+$rec->getMagequantity();
		 if($status==1){
			$data['quantitysoldconfirmed']=$data['quantitysoldconfirmed']+$rec->getMagequantity();
		 }else{
			$data['quantitysoldpending']=$data['quantitysoldpending']+$rec->getMagequantity();
		 }
	   }
		
		$amountearned = Mage::getModel('vendor/saleslist')->getCollection()
			->addFieldToFilter('cpprostatus',array('eq'=>1));
		$amountearned->getSelect()->where('mageproid ='.$mageproid);
		foreach($amountearned as $rec) {
		$data['amountearned']=$data['amountearned']+$rec->getactualparterprocost();
		$arr[]=$rec->getClearedAt();
		}
		$data['clearedat']=$arr;
		return $data;
	}
	public function createdAt($mageproid){
		$arr=array();
		$collection = Mage::getModel('catalog/product')->getCollection();
		$collection->addFieldToFilter('entity_id',array('eq' => $mageproid));
		foreach($collection as $rec) {
		$arr[]=$rec->getCreatedAt();
		}
		return $arr;
	}
	public function getDateDetail(){ 
		$session = Mage::getSingleton('customer/session'); 
		$cidvar = $session->getId();
		$collection = Mage::getModel('vendor/saleslist')->getCollection()
									->addFieldToFilter('mageproownerid',array('eq'=>$cidvar))
									->addFieldToFilter('mageorderid',array('neq'=>0));
	    $collection1 = Mage::getModel('vendor/saleslist')->getCollection()
									->addFieldToFilter('mageproownerid',array('eq'=>$cidvar))
									->addFieldToFilter('mageorderid',array('neq'=>0));
	    $collection2= Mage::getModel('vendor/saleslist')->getCollection()
									->addFieldToFilter('mageproownerid',array('eq'=>$cidvar))
									->addFieldToFilter('mageorderid',array('neq'=>0));
	    $collection3 = Mage::getModel('vendor/saleslist')->getCollection()
									->addFieldToFilter('mageproownerid',array('eq'=>$cidvar))
									->addFieldToFilter('mageorderid',array('neq'=>0));
		$first_day_of_week = date('Y-m-d', strtotime('Last Monday', time()));
		$last_day_of_week = date('Y-m-d', strtotime('Next Sunday', time()));
	    $month=$collection1->addFieldToFilter('cleared_at', array('datetime' => true,'from' =>  date('Y-m').'-01 00:00:00','to' =>  date('Y-m').'-31 23:59:59'));
	    $week=$collection2->addFieldToFilter('cleared_at', array('datetime' => true,'from' =>  $first_day_of_week.' 00:00:00','to' =>  $last_day_of_week.' 23:59:59'));
	    $day=$collection3->addFieldToFilter('cleared_at', array('datetime' => true,'from' =>  date('Y-m-d').' 00:00:00','to' =>  date('Y-m-d').' 23:59:59'));
	    $sale=0;

		$data1['year']=$sale;
		$sale1=0;
		foreach($day as $record1) {
			$sale1=$sale1+$record1->getactualparterprocost();
		}
		$data1['day']=$sale1;
		$sale2=0;
		foreach($month as $record2) {
			$sale2=$sale2+$record2->getactualparterprocost();
		}
		$data1['month']=$sale2;
		$sale3=0;
		foreach($week as $record3) {
			$sale3=$sale3+$record3->getactualparterprocost();
		}
		$data1['week']=$sale3;
	    $temp=0;
		foreach ($collection as $record) {
			$temp = $temp+$record->getactualparterprocost();
		}
		$data1['totalamount']=$temp;
		return $data1;
	}
	public function getMonthlysale(){
		$customerid = Mage::getSingleton('customer/session')->getId();
		$data=array();	
		$curryear = date('Y');
		for($i=1;$i<=12;$i++){
			$date1=$curryear."-".$i."-01 00:00:00";
			$date2=$curryear."-".$i."-31 23:59:59";
			$collection = Mage::getModel('vendor/saleslist')->getCollection();
			$collection=$collection->addFieldToFilter('mageproownerid',array('eq'=>$customerid));
			$collection=$collection->addFieldToFilter('cleared_at', array('datetime' =>true,'from' =>  $date1,'to' =>  $date2));
		    $sum=array();
		    $temp=0;
			foreach ($collection as $record) {
				$temp = $temp+$record->getactualparterprocost();
			}
			$baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
			$currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
			$price = Mage::helper('directory')->currencyConvert($temp, $baseCurrencyCode, $currentCurrencyCode);
			$data[$i]=$price;
		}
		return json_encode($data);
	}
	public function getOrderdetails(){
		$customerid = Mage::getSingleton('customer/session')->getId();
		$collection = Mage::getModel('vendor/saleslist')->getCollection();
		$collection->addFieldToFilter('mageproownerid',array('eq'=>$customerid))->setOrder('autoid','DESC');
		$userorder=array();
		$gropoid=array();
		$groporderid=array();
		$productname=array();
		$i=0; 
		foreach ($collection as $record) {
		$i++;
			if($i<=5){
				if(count($gropoid) && $record->getmagerealorderid()==$gropoid[$i-1]){
					$i--;
					$productid=$productid.",".$record->getmageproid();
					$productname=$productname.",".$record->getmageproname()." X ".$record->getmagequantity();
					$pprice=$pprice+$record->getactualparterprocost();
					$userorder[$i]=array('mageproid'=>$productid,
											'mageorderid'=>$record->getmageorderid(),
											'magerealorderid'=>$record->getmagerealorderid(),
											'mageproname'=>$productname,
											'actualparterprocost'=>$pprice,
											'cleared_at'=>$record->getcleared_at()
											);			
				}
				else{
					$productname=$record->getmageproname()." X ".$record->getmagequantity();
					$productid=$record->getmageproid();
					$pprice=$record->getactualparterprocost();
					$groporderid[$i]=$record->getmageorderid();
					$gropoid[$i]=$record->getmagerealorderid();
					$userorder[$i]=array('mageproid'=>$record->getmageproid(),
										'mageorderid'=>$record->getmageorderid(),
										'magerealorderid'=>$record->getmagerealorderid(),
										'mageproname'=>$productname,
										'actualparterprocost'=>$pprice,
										'cleared_at'=>$record->getcleared_at()
										);			
				}	
			}
		}
	return $userorder;	
	}
	public function getPaymentDetailById(){
		$customerid = Mage::getSingleton('customer/session')->getId();
		$collection = Mage::getModel('vendor/userprofile')->getCollection();
		$collection->addFieldToFilter('mageuserid',array('eq'=>$customerid));
		foreach($collection as $row){
			$paymentsource=$row->getPaymentsource();
		}
		return $paymentsource;
	}

	public function getpronamebyorder($mageorderid){
		$customerid=Mage::getSingleton('customer/session')->getCustomerId();
		$name='';
		$_collection = Mage::getModel('vendor/saleslist')->getCollection();
		$_collection->addFieldToFilter('mageorderid',$mageorderid);	
		$_collection->addFieldToFilter('mageproownerid',$customerid);	
		foreach($_collection as $res){
			$products = Mage::getModel('catalog/product')->load($res['mageproid']);
			$name = $name."<p style='float:left;'><a href='".Mage::getUrl($products->getUrlPath())."' target='blank'>".$res['mageproname']."</a> X ".intval($res['magequantity'])."&nbsp;</p>";
		}	
		return $name;		
	}

	public function getPricebyorder($mageorderid){
		$customerid=Mage::getSingleton('customer/session')->getCustomerId();
		$_collection = Mage::getModel('vendor/saleslist')->getCollection();
		$_collection->getSelect()
					->where('mageproownerid ='.$customerid)
					->columns('SUM(actualparterprocost) AS qty')
					->group('mageorderid');		
		foreach($_collection as $coll){
			if($coll->getMageorderid() == $mageorderid){
				return $coll->getQty();
			}
		}
	}
}
