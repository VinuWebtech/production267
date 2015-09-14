<?php
class Sz_Vendor_Model_Userprofile extends Mage_Core_Model_Abstract
{
    CONST IS_ONLY_CUSTOMER = 4;
    CONST IS_ONLY_VENDOR = 1;
    CONST IS_VENDOR_UNDER_APPROVAL = 0;
    public function _construct()
    {
        parent::_construct();
        $this->_init('vendor/userprofile');
    }
	
	public function getRegisterDetail($customer)
	{
        $data=Mage::getSingleton('core/app')->getRequest();
		$wholedata=$data->getParams();
		if($wholedata['wantpartner']==1){
			foreach ($customer->getAddresses() as $address)
			{
			   $customerAddress = $address->toArray();
			}
			$status=Mage::getStoreConfig('vendor/vendor_options/partner_approval')? 0:1;
			$assinstatus=Mage::getStoreConfig('vendor/vendor_options/partner_approval')? "Pending":"Vendor";
			$customerid=$customer->getId();
			$collection=Mage::getModel('vendor/userprofile');
			$collection->setwantpartner($status);
			$collection->setpartnerstatus($assinstatus);
			$collection->setmageuserid($customerid);
			$collection->setContactnumber($customerAddress['telephone']);
			$collection->setProfileurl($wholedata['profileurl']);
			$collection->save();
		}
	}
	
	public function massispartner($data){
		$wholedata=$data->getParams();
		foreach($wholedata['customer'] as $key){
			$vendorid = $key;
			$collection = Mage::getModel('vendor/userprofile')->getCollection()->addFieldToFilter('mageuserid',array('eq'=>$key));
			foreach ($collection as $row) {
					$auto=$row->getautoid();
					$collection1 = Mage::getModel('vendor/userprofile')->load($auto);
					$collection1->setwantpartner(1);
					$collection1->setpartnerstatus('Vendor');
					$collection1->save();
			}
			$users = Mage::getModel('vendor/product')->getCollection()->addFieldToFilter('userid',array('eq'=>$vendorid));
			foreach ($users as $value) {
				$allStores = Mage::app()->getStores();
				foreach ($allStores as $_eachStoreId => $val)
				{
					Mage::getModel('catalog/product_status')->updateProductStatus($value->getMageproductid(),Mage::app()->getStore($_eachStoreId)->getId(), Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
				}
				$value->setStatus(1);
				$value->save();
			}
			$customer = Mage::getModel('customer/customer')->load($key);	
			$emailTemp = Mage::getModel('core/email_template')->loadDefault('partnerapprove');
			
			$emailTempVariables = array();				
			$admin_storemail = Mage::getStoreConfig('vendor/vendor_options/adminemail');
			$adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
			$adminUsername = 'Admin';
			$emailTempVariables['myvar1'] = $customer->getName().'(Email::'.$customer->getEmail().')';
			$emailTempVariables['myvar2'] =  Mage::getUrl('vendor/account/login', array('_secure' => true));
			
			$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
			
			$emailTemp->setSenderName($adminUsername);
			$emailTemp->setSenderEmail($adminEmail);
			$emailTemp->send($customer->getEmail(),$adminUsername,$emailTempVariables);	
			Mage::dispatchEvent('mp_approve_vendor',array('vendor'=>$customer)); 
		}		
	}

	public function massisnotpartner($data){ 
		$wholedata=$data->getParams();
		foreach($wholedata['customer'] as $key){
			$vendorid = $key;
			$collection = Mage::getModel('vendor/userprofile')->getCollection();
			$collection->getSelect()->where('mageuserid ='.$key);
			foreach ($collection as $row) {
					$auto=$row->getautoid();
					$collection1 = Mage::getModel('vendor/userprofile')->load($auto);
					$collection1->setwantpartner(0);
					$collection1->setpartnerstatus('Default User');
					$collection1->save();
			}
			$users = Mage::getModel('vendor/product')->getCollection()->addFieldToFilter('userid',array('eq'=>$vendorid));
			foreach ($users as $value) {
				$id = $value->getMageproductid();
				$magentoProductModel = Mage::getModel('catalog/product')->load($id);
				$allStores = Mage::app()->getStores();
				foreach ($allStores as $_eachStoreId => $val)
				{
					Mage::getModel('catalog/product_status')->updateProductStatus($value->getMageproductid(),Mage::app()->getStore($_eachStoreId)->getId(), Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
				}
				$magentoProductModel->setStatus(2)->save();
				$value->setStatus(2);
				$value->save();
			}
			$customer = Mage::getModel('customer/customer')->load($key);	
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
			$emailTemp->send($customer->getEmail(),$adminUsername,$emailTempVariables);			
			Mage::dispatchEvent('mp_disapprove_vendor',array('vendor'=>$customer));
		}		
	}

	public function denypartner($data){ 
		$wholedata=$data->getParams();
		$vendorid = $wholedata['vendorid'];
		$collection = Mage::getModel('vendor/userprofile')->getCollection()
							->addFieldToFilter('mageuserid',array('eq'=>$vendorid));
		foreach ($collection as $row) {
				$auto=$row->getautoid();
				$collection1 = Mage::getModel('vendor/userprofile')->load($auto);
				$collection1->setwantpartner(0);
				$collection1->setpartnerstatus('Default User');
				$collection1->save();
		}
		$users = Mage::getModel('vendor/product')->getCollection()->addFieldToFilter('userid',array('eq'=>$vendorid));
		foreach ($users as $value) {
			$id = $value->getMageproductid();
			$magentoProductModel = Mage::getModel('catalog/product')->load($id);
			$allStores = Mage::app()->getStores();
			foreach ($allStores as $_eachStoreId => $val)
			{
				Mage::getModel('catalog/product_status')->updateProductStatus($value->getMageproductid(),Mage::app()->getStore($_eachStoreId)->getId(), Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
			}
			$magentoProductModel->setStatus(2)->save();
			$value->setStatus(2);
			$value->save();
		}
		$customer = Mage::getModel('customer/customer')->load($vendorid);	
		$emailTemp = Mage::getModel('core/email_template')->loadDefault('partnerdeny');
		$emailTempVariables = array();				
		$admin_storemail = Mage::getStoreConfig('vendor/vendor_options/adminemail');
		$adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
		$adminUsername = 'Admin';
		$emailTempVariables['myvar1'] = $customer->getName();
		$emailTempVariables['myvar2'] = $wholedata['vendor_deny_reason'];
		
		$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
		
		$emailTemp->setSenderName($adminUsername);
		$emailTemp->setSenderEmail($adminEmail);
		$emailTemp->send($customer->getEmail(),$adminUsername,$emailTempVariables);
		return $customer->getName();
	}
	
	public function getPartnerProfileById($partnerId) {
        $data = array();
        if ($partnerId != '') {
            $collection = Mage::getModel('vendor/userprofile')->getCollection();
            $collection->addFieldToFilter('mageuserid',array('eq'=>$partnerId));
			$user = Mage::getModel('customer/customer')->load($partnerId);
			$name=explode(' ',$user->getName());
			foreach ($collection as $record) {
				$bannerpic=$record->getbannerpic();
				$logopic=$record->getlogopic();
				$countrylogopic=$record->getcountrypic();
				if(strlen($bannerpic)<=0){$bannerpic='banner-image.png';}
				if(strlen($logopic)<=0){$logopic='noimage.png';}
				if(strlen($countrylogopic)<=0){$countrylogopic='';}
				$data= array(
						'firstname'=>$name[0],
						'lastname'=>$name[1],
						'email'=>$user->getEmail(),
						'twitterid'=>$record->gettwitterid(),
						'facebookid'=>$record->getfacebookid(),
						'contactnumber'=>$record->getcontactnumber(),
						'bannerpic'=>$bannerpic,
						'logopic'=>$logopic,
						'complocality'=>$record->getcomplocality(),
						'countrypic'=>$countrylogopic,
						'meta_keyword'=>$record->getMetaKeyword(),					
						'meta_description'=>$record->getMetaDescription(),
						'compdesi'=>$record->getcompdesi(),
						'returnpolicy'=>$record->getReturnpolicy(),
						'shippingpolicy'=>$record->getShippingpolicy(),
						'profileurl'=>$record->getProfileurl(),
						'shoptitle'=>$record->getShoptitle(),
						'backgroundth'=>$record->getbackgroundth(),
						'wantpartner'=>$record->getwantpartner()
					);
			}
			return $data;
		}
    } 
	
	public function isPartner($vendorId = null){
		$partnerId=Mage::getSingleton('customer/session')->getCustomerId();
		if($partnerId=='' && Mage::registry('current_customer'))
			$partnerId=Mage::registry('current_customer')->getId();
        if($partnerId=='')
            $partnerId=$vendorId;
		if ($partnerId != '') {
			$data=self::IS_ONLY_CUSTOMER;
			$collection = Mage::getModel('vendor/userprofile')->getCollection();
            $collection->addFieldToFilter('mageuserid',array('eq'=>$partnerId));
			foreach ($collection as $record) {
				$data=$record->getwantpartner();
			}
			return $data;
		}
	}
	public function isRightVendor($productid){
		$customerid=Mage::getSingleton('customer/session')->getCustomerId();
		$data=0;
		$product=Mage::getModel('vendor/product')->getCollection()
													 ->addFieldToFilter('userid',array('eq'=>$customerid))
												     ->addFieldToFilter('mageproductid',array('eq'=>$productid));
		foreach ($product as $record){
				$data=1;
		}
		return $data;											 
	}
	public function getpaymentmode(){
		$partnerId=Mage::registry('current_customer')->getId();
		$collection = Mage::getModel('vendor/userprofile')->getCollection();
        $collection->addFieldToFilter('mageuserid',array('eq'=>$partnerId));
		foreach ($collection as $record) {
			$data=$record->getPaymentsource();
		}
		return $data;
	}
	
	public function assignProduct($customer,$sid){
		$productids=explode(',',$sid);
		foreach($productids as $proid){
			$userid='';
			$product = Mage::getModel('catalog/product')->load($proid);
			if($product->getname()){   
				$collection = Mage::getModel('vendor/product')->getCollection()->addFieldToFilter('mageproductid',array('eq'=>$proid));
				foreach($collection as $coll){
				   $userid = $coll['userid'];
				}
				if($userid){
					Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('The product is already assign to other vendor.'));
				}
				else{
					$collection1 = Mage::getModel('vendor/product');
					$collection1->setMageproductid($proid);
					$collection1->setUserid($customer->getId());
					$collection1->setStatus($product->getStatus());
					$collection1->setAdminassign(1);
					$collection1->setWebsiteIds(array(Mage::app()->getStore()->getStoreId()));
					$collection1->save();

					Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Products has been successfully assigned to vendor.'));
				}
			} else {
				Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('adminhtml')->__("Product with id")." ".$proid." ".Mage::helper('adminhtml')->__("doesn't exist."));
			} 
		}
	}

	public function unassignProduct($customer,$sid){
		$productids=explode(',',$sid);
		foreach($productids as $proid){
			$userid='';
			$product = Mage::getModel('catalog/product')->load($proid);
			if($product->getname()){   
				$collection = Mage::getModel('vendor/product')->getCollection()->addFieldToFilter('mageproductid',array('eq'=>$proid));
				foreach($collection as $coll){
					$coll->delete();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Products has been successfully unassigned to vendor.'));
			} else {
				Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('adminhtml')->__("Product with id")." ".$proid." ".Mage::helper('adminhtml')->__("doesn't exist."));
			} 
		}
	}
	
	public function isCustomerProducttemp($magentoProductId){
		$collection = Mage::getModel('vendor/product')->getCollection();
		$collection->addFieldToFilter('mageproductid',array('eq'=>$magentoProductId));
		$userid='';
		foreach($collection as $record){
		$userid=$record->getuserid();
		}
		$collection1 = Mage::getModel('vendor/userprofile')->getCollection()->addFieldToFilter('mageuserid',array('eq'=>$userid));
		foreach($collection1 as $record1){
		$status=$record1->getWantpartner();
		}
		if($status!=1){
			$userid='';
		}
		
		return array('productid'=>$magentoProductId,'userid'=>$userid);
	}

}
