<?php
Class Sz_Vendor_Model_Observer
{
	public function deleteCustomer($observer){
		$vendorid=$observer->getCustomer()->getId();
		$vendors=Mage::getModel('vendor/userprofile')->getCollection()
												->addFieldToFilter('mageuserid',array('eq'=>$vendorid));
		foreach($vendors as $vendor){ $vendor->delete(); }
		
		$vendorpro= Mage::getModel('vendor/product')->getCollection()
							->addFieldToFilter('userid',array('eq'=>$vendorid));
		foreach($vendorpro as $pro){
			$allStores = Mage::app()->getStores();
			foreach ($allStores as $_eachStoreId => $val){
				Mage::getModel('catalog/product_status')->updateProductStatus($pro->getMageproductid(),Mage::app()->getStore($_eachStoreId)->getId(), Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
			}
			$pro->delete();
		}
	}

	public function CustomerRegister($observer){
	$data=Mage::getSingleton('core/app')->getRequest();
		if($data->getParam('wantpartner')==1){
			$customer = $observer->getCustomer();
			Mage::getModel('vendor/userprofile')->getRegisterDetail($customer);
			$emailTemp = Mage::getModel('core/email_template')->loadDefault('partnerrequest');
			
			$emailTempVariables = array();
			$admin_storemail = Mage::getStoreConfig('vendor/vendor_options/adminemail');
			$adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
			$adminUsername = 'Admin';
			$emailTempVariables['myvar1'] =  $customer->getName().'(Email::'.$customer->getEmail().')';
			$emailTempVariables['myvar2'] = Mage::getUrl('vendor/adminhtml_partners');
			
			$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
			
			$emailTemp->setSenderName($customer->getName());
			$emailTemp->setSenderEmail($customer->getEmail());
			$emailTemp->send($adminEmail,$customer->getName(),$emailTempVariables);
		}
	}
	public function DeleteProduct($observer) { 
		$collection = Mage::getModel('vendor/product')->getCollection()
														   ->addFieldToFilter('mageproductid ',$observer->getProduct()->getId());
		foreach($collection as $data){			
			Mage::getModel('vendor/product')->load($data['index_id'])->delete();			
		}		
	}
	
	public function afterPlaceOrder($observer) { 
		$lastOrderId=$observer->getOrder()->getId();
		$order = Mage::getModel('sales/order')->load($lastOrderId);
		Mage::getModel('vendor/saleslist')->getProductSalesCalculation($order);	
	}
	
	
	public function commissionCalculationOnComplete($observer){
	    $order = $observer->getOrder();
	    if($order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE){
	    	Mage::getModel('vendor/saleslist')->getCommsionCalculation($order);
	    }
	}
	
	public function afterSaveCustomer($observer){
		$customer=$observer->getCustomer();
		$customerid=$customer->getId();
		$isPartner= Mage::getModel('vendor/userprofile')->isPartner();
		if($isPartner==1){
			$data=$observer->getRequest();
			$sid = $data->getParam('vendorassignproid');
			$unassignproid = $data->getParam('vendorunassignproid');
			$partner_type = $data->getParam('partnertype');
			if($partner_type==2)
			{
				$collectionselectdelete = Mage::getModel('vendor/userprofile')->getCollection();
				$collectionselectdelete->addFieldToFilter('mageuserid',array($customerid));
				foreach($collectionselectdelete as $delete){
					$autoid=$delete->getautoid();
				}
				$collectiondelete = Mage::getModel('vendor/userprofile')->load($autoid);
				$collectiondelete->delete();
				$customer = Mage::getModel('customer/customer')->load($customerid);	
				$emailTemp = Mage::getModel('core/email_template')->loadDefault('partnerdisapprove');
			
				$emailTempVariables = array();		
				$admin_storemail = Mage::getStoreConfig('vendor/vendor_options/adminemail');
				$adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');	
				$adminUsername = 'Admin';
				$emailTempVariables['myvar1'] = $customer->getName();
				$emailTempVariables['myvar2'] = Mage::helper('customer')->getLoginUrl();
				
				$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
				
				$emailTemp->setSenderName($adminUsername);
				$emailTemp->setSenderEmail($adminEmail);
				$emailTemp->send($customer->getEmail(),$Username,$emailTempVariables);	
			}
			if($sid !=''||$sid!= 0){
				Mage::getModel('vendor/userprofile')->assignProduct($customer,$sid);
			}
			if($unassignproid !=''||$unassignproid!= 0){
				Mage::getModel('vendor/userprofile')->unassignProduct($customer,$unassignproid);
			}
			$wholedata=$data->getParams();
			$collectionselect = Mage::getModel('vendor/saleperpartner')->getCollection();
			$collectionselect->addFieldToFilter('mageuserid',array('eq'=>$customer->getId()));
			if(count($collectionselect)==1){
			    foreach($collectionselect as $verifyrow){
				$autoid=$verifyrow->getautoid();
				}
				
				$collectionupdate = Mage::getModel('vendor/saleperpartner')->load($autoid);
				$collectionupdate->setcommision($wholedata['commision']);
				$collectionupdate->save();
				}
			else{
				$collectioninsert=Mage::getModel('vendor/saleperpartner');
				$collectioninsert->setmageuserid($customer->getId());
				$collectioninsert->setcommision($wholedata['commision']);
				$collectioninsert->save();
			}

			/*Save vendor info*/
			$collection = Mage::getModel('vendor/userprofile')->getCollection();
			$collection->addFieldToFilter('mageuserid',array('eq'=>$customer->getId()));
			foreach($collection as  $value){ 
				$data = $value; 
				$value->settwitterid($wholedata['twitterid']);
				$value->setfacebookid($wholedata['facebookid']);
				$value->setcontactnumber($wholedata['contactnumber']);
				$value->setshoptitle($wholedata['shoptitle']);
				$value->setcomplocality($wholedata['complocality']);
				$value->setMetaKeyword($wholedata['meta_keyword']);

				if($wholedata['compdesi']){
					$wholedata['compdesi'] = str_replace('script', '', $wholedata['compdesi']);
				}
				$value->setcompdesi($wholedata['compdesi']);

				if($wholedata['returnpolicy']){
					$wholedata['returnpolicy'] = str_replace('script', '', $wholedata['returnpolicy']);
				}
				$value->setReturnpolicy($wholedata['returnpolicy']);

				if($wholedata['shippingpolicy']){
					$wholedata['shippingpolicy'] = str_replace('script', '', $wholedata['shippingpolicy']);
				}
				$value->setShippingpolicy($wholedata['shippingpolicy']);
				
				$value->setMetaDescription($wholedata['meta_description']);
				$target =Mage::getBaseDir().'/media/avatar/';
				if(strlen($_FILES['bannerpic']['name'])>0){
					$temp = explode(".",$_FILES["bannerpic"]["name"]);
                    $img1 = $temp[0].rand(1,99999).$loid.'.'.end($temp);
					$value->setbannerpic($img1);
					$targetb = $target.$img1; 
					move_uploaded_file($_FILES['bannerpic']['tmp_name'],$targetb);
				}
				if(strlen($_FILES['logopic']['name'])>0){
					$temp1 = explode(".",$_FILES["logopic"]["name"]);
                    $img2 = $temp1[0].rand(1,99999).$loid.'.'.end($temp);
					$value->setlogopic($img2);					
					$targetl = $target.$img2; 
					move_uploaded_file($_FILES['logopic']['tmp_name'],$targetl);
				}
				if (array_key_exists('countrypic', $fields)) {
					$value->setcountrypic($fields['countrypic']);
				}
				$value->save();
			}
		}
        else{
				$data=$observer->getRequest();
				$partner_type = $data->getParam('partnertype');
				$profileurl = $data->getParam('profileurl');
				$wholedata=$data->getParams();
				if($partner_type==1)
				{
					if($profileurl!=''){
						$profileurlcount = Mage::getModel('vendor/userprofile')->getCollection();
						$profileurlcount->addFieldToFilter('profileurl',$profileurl);
						if(count($profileurlcount)==0){
							$collectionselect = Mage::getModel('vendor/userprofile')->getCollection();
							$collectionselect->addFieldToFilter('mageuserid',array($customer->getId()));
							if(count($collectionselect)>=1){
								foreach($collectionselect as $coll){
										$coll->setWantpartner('1');
										$coll->setpartnerstatus('Vendor');
										$coll->setProfileurl($data->getParam('profileurl'));
										$coll->save();
								}
							}	
								else{
									$collection=Mage::getModel('vendor/userprofile');
									$collection->setwantpartner(1);
									$collection->setpartnerstatus('Vendor');
									$collection->setProfileurl($data->getParam('profileurl'));
									$collection->setmageuserid($customer->getId());
									$collection->save();
							}
							$customer = Mage::getModel('customer/customer')->load($customerid);

							$emailTemp = Mage::getModel('core/email_template')->loadDefault('partnerapprove');
			
							$emailTempVariables = array();				
							$admin_storemail = Mage::getStoreConfig('vendor/vendor_options/adminemail');
							$adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
							$adminUsername = 'Admin';
							$emailTempVariables['myvar1'] = $customer->getName().'(Email::'.$customer->getEmail().')';
							$emailTempVariables['myvar2'] = Mage::getUrl('vendor/account/login', array('_secure' => true));
							
							$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
							
							$emailTemp->setSenderName($adminUsername);
							$emailTemp->setSenderEmail($adminEmail);
							$emailTemp->send($customer->getEmail(),$Username,$emailTempVariables);

						} else {
							Mage::getSingleton('core/session')->addError('This Shop Name alreasy Exists.');
						}	
					}
					else{
						Mage::getSingleton('core/session')->addError('Enter Shop Name of Customer.');
					}
				}
			}
	}
	
	public function checkInvoiceSubmit($observer) { 
		$vendor_items_array = array();
		$invoice_vendor_ids = array();
		$event = $observer->getEvent()->getInvoice();
		foreach ($event->getAllItems() as $value) {
			$invoiceproduct = $value->getData();
			$pro_vendor_id = 0;
			$product_vendor	= Mage::getModel('vendor/product')->getCollection()
					->addFieldToFilter('mageproductid',$invoiceproduct['product_id']);
			foreach ($product_vendor as $vendorvalue) {
				if($vendorvalue->getUserid()){
					$invoice_vendor_ids[$vendorvalue->getUserid()] = $vendorvalue->getUserid();
					$pro_vendor_id = $vendorvalue->getUserid();			
				}
			}
			if($pro_vendor_id){
				$vendor_items_array[$pro_vendor_id][] = $invoiceproduct;
			}
		}
		$order = Mage::getModel('sales/order')->load($event->getOrderId());
		foreach($invoice_vendor_ids as $invoice_vendor_id){
			$fetchsale = Mage::getModel('vendor/saleslist')->getCollection();
			$fetchsale->addFieldToFilter('mageorderid',$event->getOrderId());	
			$fetchsale->addFieldToFilter('mageproownerid',$invoice_vendor_id);
			$totalprice ='';
			$orderinfo = '';
				$style='style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc";';
				$tax="<tr><td ".$style."><h3>Tax</h3></td><td ".$style."></td><td ".$style."></td><td ".$style."></td></tr><tr>";
				$options="<tr><td ".$style."><h3>Product Options</h3></td><td ".$style."></td><td ".$style."></td><td ".$style."></td></tr><tr><td ".$style."><b>Options</b></td><td ".$style."><b>Value</b></td><td ".$style."></td><td ".$style."></td></tr>";		
			foreach($fetchsale as $res){
				$orderinfo = $orderinfo."<tr>
								<td valign='top' align='left' ".$style." >".$res['mageproname']."</td>
								<td valign='top' align='left' ".$style.">".Mage::getModel('catalog/product')->load($res['mageproid'])->getSku()."</td>
								<td valign='top' align='left' ".$style." >".$res['magequantity']."</td>
								<td valign='top' align='left' ".$style.">".Mage::app()->getStore()->formatPrice($res['mageproprice'])."</td>
							 </tr>";	
		
				foreach($order->getAllItems() as $item){
					if($item->getProductId()==$res['mageproid']){
						$taxAmount=Mage::app()->getStore()->formatPrice($item->getTaxAmount());
						$tax=$tax."<tr><td ".$style."><b>Tax Amount</b></td><td ".$style."></td><td ".$style."></td><td ".$style.">".$taxAmount."</td></tr>";
						$temp=$item->getProductOptions();
						
						if (array_key_exists('options', $temp)) {
						foreach($temp['options'] as $data){
							$optionflag=1;
							$options=$options."<tr><td ".$style."><b>".$data['label']."</b></td><td ".$style.">".$data['value']."</td><td ".$style."></td><td ".$style."></td></tr>";
							}
						 }
						else {$optionflag='';}
							
					 }
				} 
				$totalprice = $totalprice+$res['mageproprice'];
				$userdata = Mage::getModel('customer/customer')->load($res['mageproownerid']);				
				$Username = $userdata['firstname'];
				$useremail = $userdata['email'];			
			}
			$vendor_info_array[$invoice_vendor_id] = $userdata;
			
			$shipcharge = $order->getShippingAmount();
			if($item->getTaxAmount()>0){
				$orderinfo=$orderinfo.$tax;
			}
			if($optionflag==1){
				$orderinfo=$orderinfo.$options;
			}
			$orderinfo = $orderinfo."</tbody><tbody><tr>
										<td align='right' style='padding:3px 9px' colspan='3'>Grandtotal</td>
										<td align='right' style='padding:3px 9px' colspan='3'><span>".Mage::app()->getStore()->formatPrice($totalprice+$item->getTaxAmount())."</span></td>
									</tr>";
					
			$billingId = $order->getBillingAddress()->getId();
			$billaddress = Mage::getModel('sales/order_address')->load($billingId);
			$billinginfo = $billaddress['firstname'].'<br/>'.$billaddress['street'].'<br/>'.$billaddress['city'].' '.$billaddress['region'].' '.$billaddress['postcode'].'<br/>'.Mage::getModel('directory/country')->load($billaddress['country_id'])->getName().'<br/>T:'.$billaddress['telephone'];	
			
			if($order->getShippingAddress()!='')
				$shippingId = $order->getShippingAddress()->getId();
			else
				$shippingId = $billingId;
			$address = Mage::getModel('sales/order_address')->load($shippingId);				
			$shippinginfo = $address['firstname'].'<br/>'.$address['street'].'<br/>'.$address['city'].' '.$address['region'].' '.$address['postcode'].'<br/>'.Mage::getModel('directory/country')->load($address['country_id'])->getName().'<br/>T:'.$address['telephone'];	
			
			$payment = $order->getPayment()->getMethodInstance()->getTitle();
			if($order->getShippingAddress()){
				$shippingId = $order->getShippingAddress()->getId();
				$address = Mage::getModel('sales/order_address')->load($shippingId);				
				$shippinginfo = $address['firstname'].'<br/>'.$address['street'].'<br/>'.$address['city'].' '.$address['region'].' '.$address['postcode'].'<br/>'.Mage::getModel('directory/country')->load($address['country_id'])->getName().'<br/>T:'.$address['telephone'];	
				$shipping = $order->getShippingDescription();	
				$shippinfo = $shippinginfo;
				$shippingd = $shipping;		
			}
		
			$emailTemp = Mage::getModel('core/email_template')->loadDefault('szorderinvoice');
			
			$emailTempVariables = array();				
			$admin_storemail = Mage::getStoreConfig('vendor/vendor_options/adminemail');
			$adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
			$adminUsername = 'Admin';
			$emailTempVariables['myvar1'] = $res['magerealorderid'];
			$emailTempVariables['myvar2'] = $res['cleared_at'];
			$emailTempVariables['myvar4'] = $billinginfo;
			$emailTempVariables['myvar5'] = $payment;
			$emailTempVariables['myvar6'] = $shippinfo;
			$emailTempVariables['myvar9'] = $shippingd;
			$emailTempVariables['myvar8'] = $orderinfo;
			$emailTempVariables['myvar3'] =$Username;
			
			$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
			
			$emailTemp->setSenderName($adminUsername);
			$emailTemp->setSenderEmail($adminEmail);
			$emailTemp->send($useremail,$Username,$emailTempVariables);
		}
		Mage::dispatchEvent('mp_product_sold',array('itemwithvendor'=>$vendor_items_array));
	}		
}
