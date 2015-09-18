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
        $data=$observer->getRequest();
        $partner_type = $data->getParam('partnertype');
		$isPartner= Mage::getModel('vendor/userprofile')->isPartner();
		if ($isPartner==1) {
            if($partner_type==2)
            {
                Mage::getModel('vendor/userprofile')->disaprovePartner($customer->getId());
            }
		} else {
            if($partner_type==1)
            {
                 Mage::getModel('vendor/userprofile')->approvePartner($customer->getId());
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
