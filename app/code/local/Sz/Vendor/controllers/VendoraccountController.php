<?php

require_once 'Mage/Customer/controllers/AccountController.php';
class Sz_Vendor_VendoraccountController extends Mage_Customer_AccountController{	
    public function indexAction(){		
		$this->loadLayout();     
		$this->renderLayout();
    }
    
    public function newAction(){
		$set=$this->getRequest()->getParam('set');
		$type=$this->getRequest()->getParam('type');
		if(isset($set) && isset($type)){
			$allowedsets=explode(',',Mage::getStoreConfig('vendor/vendor_options/attributesetid'));
			$allowedtypes=explode(',',Mage::getStoreConfig('vendor/vendor_options/allow_for_vendor'));
			if(!in_array($type,$allowedtypes) || !in_array($set,$allowedsets)){
				Mage::getSingleton('core/session')->addError(Mage::helper('vendor')->__('Product Type Invalide Or Not Allowed'));
			    $this->_redirect('vendor/vendoraccount/new/');
			}
			Mage::getSingleton('core/session')->setAttributeSet($set);
			switch($type){
				case "simple":
					$this->loadLayout(array('default','vendor_account_simpleproduct'));
					$this->getLayout()->getBlock('head')->setTitle(Mage::helper('vendor')->__('Vendor Product Type: Simple Product'));
					break;
				case "downloadable":
					$this->loadLayout(array('default','vendor_account_downloadableproduct'));
					$this->getLayout()->getBlock('head')->setTitle(Mage::helper('vendor')->__('Vendor Product Type: Downloabable Product'));
					break;
				case "virtual":
					$this->loadLayout(array('default','vendor_account_virtualproduct'));
					$this->getLayout()->getBlock('head')->setTitle(Mage::helper('vendor')->__('Vendor Product Type: Virtual Product'));
					break;
				case "configurable":
					$this->loadLayout(array('default','vendor_account_configurableproduct'));
					$this->getLayout()->getBlock('head')->setTitle( Mage::helper('vendor')->__('Vendor Product Type: Configurable Product'));
					break;
			}
			Mage::dispatchEvent('mp_bundalproduct',array('layout'=>$this,'type'=>$type));
			Mage::dispatchEvent('mp_groupedproduct',array('layout'=>$this,'type'=>$type));
			
			$this->_initLayoutMessages('catalog/session');
			$this->renderLayout();
		}else{
		  $this->loadLayout(array('default','vendor_vendoraccount_newproduct'));     
		  $this->renderLayout();
		}
	}

    public function categorytreeAction(){
		$data = $this->getRequest()->getParams();
		$category_model = Mage::getModel("catalog/category");
		$category = $category_model->load($data["CID"]);
		$children = $category->getChildren();
		$all = explode(",",$children);$result_tree = "";$ml = $data["ML"]+20;$count = 1;$total = count($all);
		$plus = 0;
		
		foreach($all as $each){
			$count++;
			$_category = $category_model->load($each);
			if(count($category_model->getResource()->getAllChildren($category_model->load($each)))-1 > 0){
				$result[$plus]['counting']=1;  			
			}else{
				$result[$plus]['counting']=0;
			}
			$result[$plus]['id']= $_category['entity_id'];
			$result[$plus]['name']= $_category->getName();

			$categories = explode(",",$data["CATS"]);
			if($data["CATS"] && in_array($_category["entity_id"],$categories)){
				$result[$plus]['check']= 1;
			}else{
				$result[$plus]['check']= 0;
			}
			$plus++;
		}
		echo json_encode($result);
	}
	
	/*save All product*/
	public function simpleproductAction(){
		if($this->getRequest()->isPost()){
			if (!$this->_validateFormKey()) {
             $this->_redirect('vendor/vendoraccount/new/');
            }
			 
			list($data, $errors) = $this->validatePost();
			$wholedata=$this->getRequest()->getParams();
			if(empty($errors)){		
				Mage::getModel('vendor/product')->saveSimpleNewProduct($wholedata);
				$status=Mage::getStoreConfig('vendor/vendor_options/partner_approval');
				if($status==1){
					$vendorId = Mage::getSingleton('customer/session')->getCustomer()->getId();
					$customer = Mage::getModel('customer/customer')->load($vendorId);
					$cfname=$customer->getFirstname()." ".$customer->getLastname();
					$cmail=$customer->getEmail();
					$catagory_model = Mage::getModel('catalog/category');
					$categoriesy = $catagory_model->load($wholedata['category'][0]);
					$categoryname=$categoriesy->getName();
					$emailTemp = Mage::getModel('core/email_template')->loadDefault('approveproduct');
					$emailTempVariables = array();
					$adminname = 'Administrators';
					$admin_storemail = Mage::getStoreConfig('vendor/vendor_options/adminemail');
					$adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
					$emailTempVariables['myvar1'] = $wholedata['name'];
					$emailTempVariables['myvar2'] =$categoryname;
					$emailTempVariables['myvar3'] = $adminname;
					$emailTempVariables['myvar4'] = Mage::helper('vendor')->__('I would like to inform you that recently i have added a new product in the store.');
					$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
					$emailTemp->setSenderName($cfname);
					$emailTemp->setSenderEmail($cmail);
					$emailTemp->send($adminEmail,$adminname,$emailTempVariables);
				}
			}else{
				foreach ($errors as $message) {Mage::getSingleton('core/session')->addError($message);}
				$_SESSION['new_products_errors'] = $data;
			}
			if (empty($errors))
				Mage::getSingleton('core/session')->addSuccess(Mage::helper('vendor')->__('Your product was successfully Saved'));
			    $this->_redirect('vendor/vendoraccount/new/');
		}
		else{
			 $this->_redirect('vendor/vendoraccount/new/');
		}
	}
	public function virtualproductAction() {
		if($this->getRequest()->isPost()){
			if(!$this->_validateFormKey()) {
				$this->_redirect('vendor/vendoraccount/new/');
			}
			list($data, $errors) = $this->validatePost();
			$wholedata=$this->getRequest()->getParams();
			if(empty($errors)){		
				Mage::getModel('vendor/product')->saveVirtualNewProduct($wholedata);
				$status=Mage::getStoreConfig('vendor/vendor_options/partner_approval');
				if($status==1){
					$vendorId = Mage::getSingleton('customer/session')->getCustomer()->getId();
				    $customer = Mage::getModel('customer/customer')->load($vendorId);
					$cfname=$customer->getFirstname()." ".$customer->getLastname();
					$cmail=$customer->getEmail();
					$catagory_model = Mage::getModel('catalog/category');
					$categoriesy = $catagory_model->load($wholedata['category'][0]);
					$categoryname=$categoriesy->getName();
					$emailTemp = Mage::getModel('core/email_template')->loadDefault('approveproduct');
					$emailTempVariables = array();
					$adminname = 'Administrators';
					$admin_storemail = Mage::getStoreConfig('vendor/vendor_options/adminemail');
					$adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
					$emailTempVariables['myvar1'] = $wholedata['name'];
					$emailTempVariables['myvar2'] =$categoryname;
					$emailTempVariables['myvar3'] = $adminname;
					$emailTempVariables['myvar4'] = Mage::helper('vendor')->__('I would like to inform you that recently i have added a new product in the store.');
					$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
					$emailTemp->setSenderName($cfname);
					$emailTemp->setSenderEmail($cmail);
					$emailTemp->send($adminEmail,$adminname,$emailTempVariables);
				}
			}else{
				foreach ($errors as $message) {$this->_getSession()->addError($message);}
				$_SESSION['new_products_errors'] = $data;
			}
			if (empty($errors))
				Mage::getSingleton('core/session')->addSuccess(Mage::helper('vendor')->__('Your product was successfully Saved'));
				 $this->_redirect('vendor/vendoraccount/new/');
		}
		else{
			 $this->_redirect('vendor/vendoraccount/new/');
		}
	}
	public function downloadableproductAction() {
		if($this->getRequest()->isPost()){ 
			 if (!$this->_validateFormKey()) {
				 $this->_redirect('vendor/vendoraccount/new/');
             }
			list($data, $errors) = $this->validatePost();
			$wholedata=$this->getRequest()->getParams();
			if(empty($errors)){		
				Mage::getModel('vendor/product')->saveDownloadableNewProduct($wholedata);
				$status=Mage::getStoreConfig('vendor/vendor_options/partner_approval');
				if($status==1){
					$vendorId = Mage::getSingleton('customer/session')->getCustomer()->getId();
				    $customer = Mage::getModel('customer/customer')->load($vendorId);
					$cfname=$customer->getFirstname()." ".$customer->getLastname();
					$cmail=$customer->getEmail();
					$catagory_model = Mage::getModel('catalog/category');
					$categoriesy = $catagory_model->load($wholedata['category'][0]);
					$categoryname=$categoriesy->getName();
					$emailTemp = Mage::getModel('core/email_template')->loadDefault('approveproduct');
					$emailTempVariables = array();
					$adminname = 'Administrators';
					$admin_storemail = Mage::getStoreConfig('vendor/vendor_options/adminemail');
					$adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
					$emailTempVariables['myvar1'] = $wholedata['name'];
					$emailTempVariables['myvar2'] =$categoryname;
					$emailTempVariables['myvar3'] = $adminname;
					$emailTempVariables['myvar4'] = Mage::helper('vendor')->__('I would like to inform you that recently i have added a new product in the store.');
					$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
					$emailTemp->setSenderName($cfname);
					$emailTemp->setSenderEmail($cmail);
					$emailTemp->send($adminEmail,$adminname,$emailTempVariables);
				}
			}else{
				foreach ($errors as $message) {$this->_getSession()->addError($message);}
				$_SESSION['new_products_errors'] = $data;
			}
			if (empty($errors))
				Mage::getSingleton('core/session')->addSuccess(Mage::helper('vendor')->__('Your product was successfully Saved'));
				return $this->_redirect('vendor/vendoraccount/new/');
		}
		else{
			return $this->_redirect('vendor/vendoraccount/new/');
		}
	}
	public function configurableproductAction() {
		$wholedata=$this->getRequest()->getParam('attribute');
		$magentoProductModel = Mage::getModel('catalog/product');
		$this->_redirect('vendor/vendoraccount/addconfigurableproduct');
	}
	public function configurableproductattrAction(){
		$this->loadLayout( array('default','vendor_account_configurableproductattr'));
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
		$this->getLayout()->getBlock('head')->setTitle($this->__('Configurable Product Attribute'));
    	$this->renderLayout();
	}

	public function vieworderAction(){
		$available_vendor_item = 0;
		$customerid=Mage::getSingleton('customer/session')->getCustomerId();
		$vendor_orderslist=Mage::getModel('vendor/saleslist')->getCollection()
									 ->addFieldToFilter('mageproownerid',array('eq'=>$customerid))
									 ->addFieldToFilter('mageorderid',array('eq'=>$this->getRequest()->getParam('id')));
		foreach($vendor_orderslist as $vendor_item){
			$available_vendor_item = 1;
		}
		if($available_vendor_item){
			$this->loadLayout( array('default','vendor_vendoraccount_vieworder'));
	        $this->_initLayoutMessages('customer/session');
	        $this->_initLayoutMessages('catalog/session');
			$this->getLayout()->getBlock('head')->setTitle($this->__('View Order Details'));
	    	$this->renderLayout();
	    }else{
	    	$this->_redirect('vendor/vendoraccount/myorderhistory');
	    }
	}
	public function printorderAction(){
		$available_vendor_item = 0;
		$customerid=Mage::getSingleton('customer/session')->getCustomerId();
		$vendor_orderslist=Mage::getModel('vendor/saleslist')->getCollection()
									 ->addFieldToFilter('mageproownerid',array('eq'=>$customerid))
									 ->addFieldToFilter('mageorderid',array('eq'=>$this->getRequest()->getParam('id')));
		foreach($vendor_orderslist as $vendor_item){
			$available_vendor_item = 1;
		}
		if($available_vendor_item){
			$this->loadLayout( array('default','vendor_vendoraccount_printorder'));
	        $this->_initLayoutMessages('customer/session');
	        $this->_initLayoutMessages('catalog/session');
			$this->getLayout()->getBlock('head')->setTitle($this->__('Print Order Details'));
	    	$this->renderLayout();
    	}else{
	    	$this->_redirect('vendor/vendoraccount/myorderhistory');
	    }
	}
	
	public function addconfigurableproductAction(){
		return $this->_redirect('vendor/vendoraccount/new/');
		/*$this->loadLayout( array('default','vendor_account_configurableproduct'));
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
		$this->getLayout()->getBlock('head')->setTitle($this->__('Add Configurable Product'));
    	$this->renderLayout();*/
	}

	public function newattributeAction(){
		$this->loadLayout( array('default','vendor_account_newattribute'));
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
		$this->getLayout()->getBlock('head')->setTitle($this->__(' Manage Attribute'));
    	$this->renderLayout();
	}

    public function uploadproductsAction(){
        $this->loadLayout( array('default','vendor_account_uploadproducts'));
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Upload Product CSV'));
        $this->renderLayout();
    }

    public function uploadimagesAction(){
        $this->loadLayout( array('default','vendor_account_uploadimages'));
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Upload Product Images'));
        $this->renderLayout();
    }

    public function downloadSampleAction() {
        $vendorHelper = Mage::helper('vendor');
        $this->getResponse()->setHttpResponseCode(200)
            ->setHeader('Pragma','publi', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0', true)
            ->setHeader('Content-type', 'application/force-download')
            ->setHeader('Content-Length', filesize($vendorHelper->getSampleFilePath()))
            ->setHeader(
                    'Content-Disposition', 'attachment'.';filename='.basename($vendorHelper->getSampleFilePath())
                );
        $this->getResponse()->clearBody();
        $this->getResponse()->sendHeaders();
        readfile($vendorHelper->getSampleFilePath());
        exit;
    }


    public function downloadAction() {
        $fileId = $this->getRequest()->getParam('fileid', '');
        $fileId = urldecode($fileId);
        if ($fileId) {
            $file = Mage::getModel('vendor/uploader')->load($fileId);
            $vendorHelper = Mage::helper('vendor');
            if ($file->getStatus() == Sz_Vendor_Model_Uploader::COMPLETE) {
                $filePath = $vendorHelper->getProductImportedFileDirectory().$file->getFileName();
            } else {
                $filePath = $vendorHelper->getArchiveDirectory().$file->getFileName();
            }

            $this->getResponse()->setHttpResponseCode(200)
                ->setHeader('Pragma','publi', true)
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0', true)
                ->setHeader('Content-type', 'application/force-download')
                ->setHeader('Content-Length', filesize($filePath))
                ->setHeader(
                    'Content-Disposition', 'attachment'.';filename='.basename($filePath)
                );
            $this->getResponse()->clearBody();
            $this->getResponse()->sendHeaders();
            readfile($vendorHelper->getSampleFilePath());
            exit;
        }

    }

    public function uploadPostAction() {
        $partner = Mage::getModel('vendor/userprofile')->isPartner(Mage::getSingleton('customer/session')->getCustomerId());
        if (!$partner) {
            Mage::getSingleton('core/session')->addError(
                'Only a Vendor can upload the Product CSV.'
            );
            $this->_redirectReferer();
            return;
        }
        $vendorHelper = Mage::helper('vendor');
        $destinationPath = $vendorHelper->getProductImportedFileDirectory();
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 777, true);
        }
        try {
            $prefix = $this->_getHelper('core')
                ->getHash(date('dmyhms'),5);
            $fileName = date('ymdhms').'-'.$_FILES['product_file']['name'];
            $uploader = new Varien_File_Uploader($_FILES['product_file']);
            $uploader->setAllowedExtensions(array('csv'));
            $uploader->setAllowRenameFiles(false);
            $uploader->setFilesDispersion(false);
            if ($uploader->save($destinationPath, $fileName)) {
                Mage::getSingleton('core/session')->addSuccess(
                    Mage::helper('vendor')->__('Product CSV file has been uploaded successfully.')
                );
                $vendorFiles = Mage::getModel('vendor/uploader');
                $vendorFiles->setVendorId(Mage::getSingleton('customer/session')->getCustomerId());
                $vendorFiles->setFileName($fileName);
                $vendorFiles->setFileType(Sz_Vendor_Model_Uploader::PRODUCT_FILE_CSV);
                $vendorFiles->setAttributeSet($this->getRequest()->getParam('attribute_set'));
                $vendorFiles->setStatus(Sz_Vendor_Model_Uploader::PENDING);
                $vendorFiles->save();
                $customerid = Mage::getSingleton('customer/session')->getCustomerId();
                $vendor = Mage::getModel('customer/customer')->load($customerid);
                $email = $vendor->getEmail();
                $name = $vendor->getFirstname()." ".$vendor->getLastname();
                $adminname = 'Administrators';
                $admin_storemail = Mage::getStoreConfig('vendor/vendor_options/adminemail');
                $adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
                $emailTemp = Mage::getModel('core/email_template')->loadDefault('fileuploadnotification');
                $emailTempVariables = array();
                $emailTempVariables['myvar1'] = 'Product CSV has been uploaded by Vendor '.$name;
                $emailTempVariables['myvar2'] =$name.' (email:'.$email.')';
                $emailTempVariables['myvar3'] = $fileName;
                $processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
                $emailTemp->setSenderName($name);
                $emailTemp->setSenderEmail($email);
                $emailTemp->send($adminEmail,'Administrators',$emailTempVariables);
            }
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError(
                $e->getMessage()
            );
        }
        $this->_redirectReferer();
    }

    public function uploadImagePostAction() {
        $partner = Mage::getModel('vendor/userprofile')->isPartner(Mage::getSingleton('customer/session')->getCustomerId());
        if (!$partner) {
            Mage::getSingleton('core/session')->addError(
                'Only a Vendor can upload the Product Image Zip File.'
            );
            $this->_redirectReferer();
            return;
        }
        $vendorHelper = Mage::helper('vendor');
        $destinationPath = $vendorHelper->getProductImportedImageFileDirectory();
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 777, true);
        }
        try {
            $prefix = $this->_getHelper('core')
                ->getHash(date('dmyhms'),5);
            $fileName = date('ymdhms').'-'.$_FILES['product_image_file']['name'];
            $uploader = new Varien_File_Uploader($_FILES['product_image_file']);
            $uploader->setAllowedExtensions(array('zip'));
            $uploader->setAllowRenameFiles(false);
            $uploader->setFilesDispersion(false);
            if ($uploader->save($destinationPath, $fileName)) {
                Mage::getSingleton('core/session')->addSuccess(
                    Mage::helper('vendor')->__('Product Image Zip file has been uploaded successfully.')
                );
                $vendorFiles = Mage::getModel('vendor/uploader');
                $vendorFiles->setVendorId(Mage::getSingleton('customer/session')->getCustomerId());
                $vendorFiles->setFileName($fileName);
                $vendorFiles->setFileType(Sz_Vendor_Model_Uploader::PRODUCT_IMAGE);
                $vendorFiles->setAttributeSet($this->getRequest()->getParam('attribute_set'));
                $vendorFiles->setStatus(Sz_Vendor_Model_Uploader::PENDING);
                $vendorFiles->save();
            }

        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError(
                $e->getMessage()
            );
        }
        $this->_redirectReferer();
    }
	
	public function createattributeAction() {
		if($this->getRequest()->isPost()){
			if (!$this->_validateFormKey()) {
              return $this->_redirect('vendor/vendoraccount/newattribute/');
             }
			
			$wholedata=$this->getRequest()->getParams();
			$attributes = Mage::getModel('catalog/product')->getAttributes();

		    foreach($attributes as $a){
		            $allattrcodes = $a->getEntityType()->getAttributeCodes();
		    }
		    if(in_array($wholedata['attribute_code'], $allattrcodes)){
		    	Mage::getSingleton('core/session')->addError(Mage::helper('vendor')->__('Attribute Code already exists'));
				$this->_redirect('vendor/vendoraccount/newattribute/');
		    }else{
				list($data, $errors) = $this->validatePost();
				if(array_key_exists('attroptions', $wholedata)){
					foreach( $wholedata['attroptions'] as $c){
						$data1['.'.$c['admin'].'.'] = array($c['admin'],$c['store']);	
					}
				}else{
					$data1=array();
				}
				
				$_attribute_data = array(
									'attribute_code' => $wholedata['attribute_code'],
									'is_global' => '1',
									'frontend_input' => $wholedata['frontend_input'],
									'default_value_text' => '',
									'default_value_yesno' => '0',
									'default_value_date' => '',
									'default_value_textarea' => '',
									'is_unique' => '0',
									'is_required' => '0',
									'apply_to' => '0',
									'is_configurable' => '1',
									'is_searchable' => '0',
									'is_visible_in_advanced_search' => '1',
									'is_comparable' => '0',
									'is_used_for_price_rules' => '0',
									'is_wysiwyg_enabled' => '0',
									'is_html_allowed_on_front' => '1',
									'is_visible_on_front' => '0',
									'used_in_product_listing' => '0',
									'used_for_sort_by' => '0',
									'frontend_label' => $wholedata['attribute_code']
								);
				$model = Mage::getModel('catalog/resource_eav_attribute');
				if (!isset($_attribute_data['is_configurable'])) {
					$_attribute_data['is_configurable'] = 0;
				}
				if (!isset($_attribute_data['is_filterable'])) {
					$_attribute_data['is_filterable'] = 0;
				}
				if (!isset($_attribute_data['is_filterable_in_search'])) {
					$_attribute_data['is_filterable_in_search'] = 0;
				}
				if (is_null($model->getIsUserDefined()) || $model->getIsUserDefined() != 0) {
					$_attribute_data['backend_type'] = $model->getBackendTypeByInput($_attribute_data['frontend_input']);
				}
				$defaultValueField = $model->getDefaultValueByInput($_attribute_data['frontend_input']);
				if ($defaultValueField) {
					$_attribute_data['default_value'] = $this->getRequest()->getParam($defaultValueField);
				}
				$model->addData($_attribute_data);
				$data['option']['value'] = $data1;
				if($wholedata['frontend_input'] == 'select' || $wholedata['frontend_input'] == 'multiselect')
					$model->addData($data);
				$model->setAttributeSetId($wholedata['attribute_set_id']);
				$model->setAttributeGroupId($wholedata['AttributeGroupId']);
				$entityTypeID = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
				$model->setEntityTypeId($entityTypeID);
				$model->setEntityTypeId(Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId());
				$model->setIsUserDefined(1);
				$model->save();
				Mage::getSingleton('core/session')->addSuccess(Mage::helper('vendor')->__('Attribute Created Successfully'));
				$this->_redirect('vendor/vendoraccount/newattribute/');
			}
		}
	}

	public function quickcreateAction() {
		 if (!$this->_validateFormKey()) {
           return $this->_redirect('vendor/vendoraccount/myproductslist/');
         }
		$wholedata=$this->getRequest()->getParams();
		$id = $wholedata['mainid'];
	    Mage::getModel('vendor/product')->quickcreate($wholedata);
		$this->_redirect('vendor/vendoraccount/configurableassociate',array('id'=>$id));
		Mage::getSingleton('core/session')->addSuccess(Mage::helper('vendor')->__('Associate Product created Successfully'));
	}
	public function assignassociateAction() {
		$wholedata=$this->getRequest()->getParams();		
	    Mage::getModel('vendor/product')->editassociate($wholedata);
	    Mage::getModel('vendor/product')->saveassociate($wholedata);
	    $id = $wholedata['mainid'];
		Mage::getSingleton('core/session')->addSuccess(Mage::helper('vendor')->__('Product has been assigned successfully'));
		$this->_redirect('vendor/vendoraccount/configurableassociate',array('id'=>$id));
	}

	public function configproductAction() {
		if($this->getRequest()->isPost()){
			 if (!$this->_validateFormKey()) {
              return $this->_redirect('vendor/vendoraccount/new/');
             }
			
			list($data, $errors) = $this->validatePost();
			$wholedata=$this->getRequest()->getParams();
			if(empty($errors)){	
			$id =  Mage::getModel('vendor/product')->saveConfigNewProduct($wholedata);
			$status=Mage::getStoreConfig('vendor/vendor_options/partner_approval');
			if($status==1){
				$vendorId = Mage::getSingleton('customer/session')->getCustomer()->getId();
				$customer = Mage::getModel('customer/customer')->load($vendorId);
				$cfname=$customer->getFirstname()." ".$customer->getLastname();
				$cmail=$customer->getEmail();
				$catagory_model = Mage::getModel('catalog/category');
				$categoriesy = $catagory_model->load($wholedata['category'][0]);
				$categoryname=$categoriesy->getName();
				$emailTemp = Mage::getModel('core/email_template')->loadDefault('approveproduct');
				$emailTempVariables = array();
				$adminname = 'Administrators';
				$admin_storemail = Mage::getStoreConfig('vendor/vendor_options/adminemail');
				$adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
				$emailTempVariables['myvar1'] = $wholedata['name'];
				$emailTempVariables['myvar2'] =$categoryname;
				$emailTempVariables['myvar3'] =$adminname;
				$emailTempVariables['myvar4'] = Mage::helper('vendor')->__('I would like to inform you that recently i have added a new product in the store.');
				$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
				$emailTemp->setSenderName($cfname);
				$emailTemp->setSenderEmail($cmail);
				$emailTemp->send($adminEmail,$adminname,$emailTempVariables);
			}
			}else{
				foreach ($errors as $message) {$this->_getSession()->addError($message);}
				$_SESSION['new_products_errors'] = $data;
			}
			$attr = $wholedata['attrdata'];
			Mage::getSingleton('core/session')->addSuccess(Mage::helper('vendor')->__('Product has been Created Successfully'));
			$this->_redirect('vendor/vendoraccount/configurableassociate',array('attr'=>$attr,'id'=>$id));
		}
		else{
			return $this->_redirect('vendor/vendoraccount/new/');
		}
	}

	public function configurableassociateAction(){
		$this->loadLayout( array('default','vendor_account_configurableassociate'));
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
		$this->getLayout()->getBlock('head')->setTitle($this->__('Add Associate Product'));
    	$this->renderLayout();
	}
	
	public function myproductslistAction(){
		$this->loadLayout( array('default','vendor_account_productlist'));
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
		$this->getLayout()->getBlock('head')->setTitle( Mage::helper('vendor')->__('My Product List'));
    	$this->renderLayout();
	}

    public function productcsvstatusAction(){
        $this->loadLayout( array('default','vendor_account_productcsvstatus'));
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
        $this->getLayout()->getBlock('head')->setTitle( Mage::helper('vendor')->__('Product CSV List'));
        $this->renderLayout();
    }


	public function becomepartnerAction(){
		if($this->getRequest()->isPost()){ 
			 if (!$this->_validateFormKey()) {
              return $this->_redirect('vendor/vendoraccount/becomepartner/');
             }
			
			$wholedata=$this->getRequest()->getParams();
			Mage::getModel('vendor/product')->saveBecomePartnerStatus($wholedata);
			Mage::getSingleton('core/session')->addSuccess(Mage::helper('vendor')->__('Your request to become vendor was successfully send to admin'));
			$this->_redirect('vendor/vendoraccount/becomepartner/');
		}
		else{
			$this->loadLayout( array('default','vendor_account_becomepartner'));
			$this->_initLayoutMessages('customer/session');
			$this->_initLayoutMessages('catalog/session');
			$this->getLayout()->getBlock('head')->setTitle( Mage::helper('vendor')->__('Vendor Request Panel'));
			$this->renderLayout();
		}
	}
	
	public function myorderhistoryAction(){
		$this->loadLayout( array('default','vendor_account_orderhistory'));
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
		$this->getLayout()->getBlock('head')->setTitle( Mage::helper('vendor')->__('My Order History'));
    	$this->renderLayout();
	}
	
	public function editapprovedsimpleAction() {
		if($this->getRequest()->isPost()){
			 if (!$this->_validateFormKey()) {
              return $this->_redirect('vendor/vendoraccount/myproductslist/');
             }
			
			list($data, $errors) = $this->validatePost();
			$id= $this->getRequest()->getParam('productid');
			$customerid=Mage::getSingleton('customer/session')->getCustomerId();
			$collection_product = Mage::getModel('vendor/product')->getCollection()->addFieldToFilter('mageproductid',array('eq'=>$id))->addFieldToFilter('userid',array('eq'=>$customerid));
            if(count($collection_product))
            {
				if(empty($errors)){	
					Mage::getSingleton('core/session')->setEditProductId($id);
					Mage::getModel('vendor/product')->editProduct($id,$this->getRequest()->getParams());
					Mage::getSingleton('core/session')->addSuccess(Mage::helper('vendor')->__('Your Product Is Been Sucessfully Updated'));
					$this->_redirect('vendor/vendoraccount/myproductslist/');
				}else{
					foreach ($errors as $message) {Mage::getSingleton('core/session')->addError($message);}
					$_SESSION['new_products_errors'] = $data;
					$this->_redirect('vendor/vendoraccount/editapprovedsimple',array('id'=>$id));
				}
		    }
		    else
		    {
				$this->_redirect('vendor/vendoraccount/editapprovedsimple',array('id'=>$id));
			}	
		}
		else{
			$urlid=$this->getRequest()->getParam('id');
			$loadpro =Mage::getModel('catalog/product')->load($urlid);
			if($loadpro ->getTypeId()!='simple'){
				$type_id = $loadpro ->getTypeId();
				if($type_id=='virtual')
					$this->_redirect('vendor/vendoraccount/editapprovedvirtual',array('id'=>$urlid));
				if($type_id=='downloadable')
					$this->_redirect('vendor/vendoraccount/editapproveddownloadable',array('id'=>$urlid));	
				if($type_id=='configurable')
					$this->_redirect('vendor/vendoraccount/editapprovedconfigurable',array('id'=>$urlid));
			}
			$this->loadLayout( array('default','vendor_account_simpleproductedit'));
			$this->_initLayoutMessages('customer/session');
			$this->_initLayoutMessages('catalog/session');
			$this->getLayout()->getBlock('head')->setTitle( Mage::helper('vendor')->__('Vendor: Edit Simple Magento Product'));
			$this->renderLayout();
		}
	}
	public function editapprovedvirtualAction() {
		if($this->getRequest()->isPost()){
			if (!$this->_validateFormKey()) {
				return $this->_redirect('vendor/vendoraccount/myproductslist/');
			}
			list($data, $errors) = $this->validatePost();
			$id= $this->getRequest()->getParam('productid');
			$customerid=Mage::getSingleton('customer/session')->getCustomerId();
			$collection_product = Mage::getModel('vendor/product')->getCollection()->addFieldToFilter('mageproductid',array('eq'=>$id))->addFieldToFilter('userid',array('eq'=>$customerid));
			if(count($collection_product)){
				if(empty($errors)){     
					Mage::getSingleton('core/session')->setEditProductId($id);
					Mage::getModel('vendor/product')->editVirtualProduct($id,$this->getRequest()->getParams());
					Mage::getSingleton('core/session')->addSuccess(Mage::helper('vendor')->__('Your Product Is Been Sucessfully Updated'));
					$this->_redirect('vendor/vendoraccount/myproductslist/');
				}else{
					foreach ($errors as $message) {Mage::getSingleton('core/session')->addError($message);}
					$_SESSION['new_products_errors'] = $data;
					$this->_redirect('vendor/vendoraccount/editapprovedvirtual',array('id'=>$id));
				}
			}else{
				$this->_redirect('vendor/vendoraccount/editapprovedvirtual',array('id'=>$id));
			}					
		}else{
			$urlid=$this->getRequest()->getParam('id');
			$loadpro =Mage::getModel('catalog/product')->load($urlid);
			if($loadpro ->getTypeId()!='virtual'){
				$type_id = $loadpro ->getTypeId();
				if($type_id=='simple')
					$this->_redirect('vendor/vendoraccount/editapprovedsimple',array('id'=>$urlid));
				if($type_id=='downloadable')
					$this->_redirect('vendor/vendoraccount/editapproveddownloadable',array('id'=>$urlid));	
				if($type_id=='configurable')
					$this->_redirect('vendor/vendoraccount/editapprovedconfigurable',array('id'=>$urlid));
			}
			$this->loadLayout( array('default','vendor_account_virtualproductedit'));
			$this->_initLayoutMessages('customer/session');
			$this->_initLayoutMessages('catalog/session');
			$this->getLayout()->getBlock('head')->setTitle( Mage::helper('vendor')->__('Vendor: Edit Virtual Magento Product'));
			$this->renderLayout();
			}
        }
	public function editapproveddownloadableAction() {
		if($this->getRequest()->isPost()){
			if (!$this->_validateFormKey()) {
				$this->_redirect('vendor/vendoraccount/myproductslist/');
			}
			list($data, $errors) = $this->validatePost();
			$id= $this->getRequest()->getParam('productid');
			$customerid=Mage::getSingleton('customer/session')->getCustomerId();
			$collection_product = Mage::getModel('vendor/product')->getCollection()->addFieldToFilter('mageproductid',array('eq'=>$id))->addFieldToFilter('userid',array('eq'=>$customerid));
			if(count($collection_product)){
				if(empty($errors)){     
					Mage::getSingleton('core/session')->setEditProductId($id);
					Mage::getModel('vendor/product')->editDownloadableProduct($id,$this->getRequest()->getParams());
					Mage::getSingleton('core/session')->addSuccess(Mage::helper('vendor')->__('Your Product Is Been Sucessfully Updated'));
					$this->_redirect('vendor/vendoraccount/myproductslist/');
				}else{
					foreach ($errors as $message) {Mage::getSingleton('core/session')->addError($message);}
					$_SESSION['new_products_errors'] = $data;
					$this->_redirect('vendor/vendoraccount/editapproveddownloadable',array('id'=>$id));
				}
			}else{
				$this->_redirect('vendor/vendoraccount/editapproveddownloadable',array('id'=>$id));
			}	        
		}else{
			$urlid=$this->getRequest()->getParam('id');
			$loadpro =Mage::getModel('catalog/product')->load($urlid);
			if($loadpro ->getTypeId()!='downloadable'){
				$type_id = $loadpro ->getTypeId();
				if($type_id=='simple')
					$this->_redirect('vendor/vendoraccount/editapprovedsimple',array('id'=>$urlid));
				if($type_id=='virtual')
					$this->_redirect('vendor/vendoraccount/editapprovedvirtual',array('id'=>$urlid));
				if($type_id=='configurable')
					$this->_redirect('vendor/vendoraccount/editapprovedconfigurable',array('id'=>$urlid));
			}
			$this->loadLayout( array('default','vendor_account_downloadableproductedit'));
			$this->_initLayoutMessages('customer/session');
			$this->_initLayoutMessages('catalog/session');
			$this->getLayout()->getBlock('head')->setTitle( Mage::helper('vendor')->__('Vendor: Edit Downloadable Magento Product'));
			$this->renderLayout();
		}
	}
	public function editapprovedconfigurableAction() {
		if($this->getRequest()->isPost()){
			if(!$this->_validateFormKey()){
				 $this->_redirect('vendor/vendoraccount/myproductslist/');
			}
			list($data, $errors) = $this->validatePost();
			$id= $this->getRequest()->getParam('productid');
			$customerid=Mage::getSingleton('customer/session')->getCustomerId();
			$collection_product = Mage::getModel('vendor/product')->getCollection()->addFieldToFilter('mageproductid',array('eq'=>$id))->addFieldToFilter('userid',array('eq'=>$customerid));
			if(count($collection_product)){
				if(empty($errors)){	
					Mage::getSingleton('core/session')->setEditProductId($id);
					Mage::getModel('vendor/product')->editProduct($id,$this->getRequest()->getParams());
					Mage::getSingleton('core/session')->addSuccess(Mage::helper('vendor')->__('Your Product Is Been Sucessfully Updated'));
					$this->_redirect('vendor/vendoraccount/myproductslist/');
				}else{
					foreach ($errors as $message) {Mage::getSingleton('core/session')->addError($message);}
					$_SESSION['new_products_errors'] = $data;
					$this->_redirect('vendor/vendoraccount/editapprovedconfigurable',array('id'=>$id));
				}
			}else{
				$this->_redirect('vendor/vendoraccount/editapprovedconfigurable',array('id'=>$id));
			}				
		}else{
			$urlid=$this->getRequest()->getParam('id');
			$loadpro =Mage::getModel('catalog/product')->load($urlid);
			if($loadpro ->getTypeId()!='configurable'){
				$type_id = $loadpro ->getTypeId();
				if($type_id=='simple')
					$this->_redirect('vendor/vendoraccount/editapprovedsimple',array('id'=>$urlid));
				if($type_id=='virtual')
					$this->_redirect('vendor/vendoraccount/editapprovedvirtual',array('id'=>$urlid));
				if($type_id=='downloadable')
					$this->_redirect('vendor/vendoraccount/editapproveddownloadable',array('id'=>$urlid));
			}
			$this->loadLayout( array('default','vendor_account_configurableproductedit'));
			$this->_initLayoutMessages('customer/session');
			$this->_initLayoutMessages('catalog/session');
			$this->getLayout()->getBlock('head')->setTitle( Mage::helper('vendor')->__('Vendor: Edit Configurable Magento Product'));
			$this->renderLayout();
		}
	}
	
	public function deleteAction(){
		$urlapp=$_SERVER['REQUEST_URI'];
		$record=Mage::getModel('vendor/product')->deleteProduct($urlapp);
		if($record==1){
			Mage::getSingleton('core/session')->addError( Mage::helper('vendor')->__('YOU ARE NOT AUTHORIZE TO DELETE THIS PRODUCT..'));	
		}else{
			Mage::getSingleton('core/session')->addSuccess( Mage::helper('vendor')->__('Your Product Has Been Sucessfully Deleted From Your Account'));
		}  
		$this->_redirect('vendor/vendoraccount/myproductslist/');
	}

	public function massdeletevendorproAction(){
		if($this->getRequest()->isPost()){
			if(!$this->_validateFormKey()){
				 $this->_redirect('vendor/vendoraccount/myproductslist/');
			}
			$ids= $this->getRequest()->getParam('product_mass_delete');
			$customerid=Mage::getSingleton('customer/session')->getCustomerId();
			$unauth_ids = array();
			Mage::register("isSecureArea", 1);
			Mage :: app("default") -> setCurrentStore( Mage_Core_Model_App :: ADMIN_STORE_ID );
			foreach ($ids as $id){				
			    $collection_product = Mage::getModel('vendor/product')->getCollection()
			    							->addFieldToFilter('mageproductid',array('eq'=>$id))
				    						->addFieldToFilter('userid',array('eq'=>$customerid));
				if(count($collection_product)) {					
					Mage::getModel('catalog/product')->load($id)->delete();
					$collection=Mage::getModel('vendor/product')->getCollection()
									->addFieldToFilter('mageproductid',array('eq'=>$id));
					foreach($collection as $row){
						$row->delete();
					}
				}else{
					array_push($unauth_ids, $id);
				}
			}
		}
		if(count($unauth_ids)){
			Mage::getSingleton('core/session')->addError( Mage::helper('vendor')->__('You are not authorized to delete products with id '.implode(",", $unauth_ids)));	
		}else{
			Mage::getSingleton('core/session')->addSuccess( Mage::helper('vendor')->__('Products has been sucessfully deleted from your account'));
		}  
		$this->_redirect('vendor/vendoraccount/myproductslist/');
	}
	
	public function mydashboardAction(){
		$this->loadLayout( array('default','vendor_account_dashboard'));
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
		$this->getLayout()->getBlock('head')->setTitle( Mage::helper('vendor')->__('My Dashboard'));
    	$this->renderLayout();
	}
	
	public function verifyskuAction(){
		$sku=$this->getRequest()->getParam('sku');
		$id = Mage::getModel('catalog/product')->getIdBySku($sku);
		if ($id){ $avl=0; }
		else{ $avl=1; } 
		echo json_encode(array("avl"=>$avl));
	}
	public function deleteimageAction(){
		$data= $this->getRequest()->getParams();
		$_product = Mage::getModel('catalog/product')->load($data['pid'])->getMediaGalleryImages();
		$main = explode('/',$data['file']);
		foreach($_product as $_image) { 
			$arr = explode('/',$_image['path']);
			if(array_pop($arr) != array_pop($main)){
				$newimage = $_image['file'];
				$id = $_image['value_id'];
				break;
			}		
		}
		$mediaApi = Mage::getModel("catalog/product_attribute_media_api");
		$mediaApi->remove($data['pid'], $data['file']);
		if($newimage){
			$objprod=Mage::getModel('catalog/product')->load($data['pid']);
			$objprod->setSmallImage($newimage);
			$objprod->setImage($newimage);
			$objprod->setThumbnail($newimage);
			$objprod->save();	
		}
	}
	
	private function validatePost(){
		$errors = array();
		$data = array();
		foreach( $this->getRequest()->getParams() as $code => $value){
			switch ($code) :
				case 'name':
					if(trim($value) == '' ){$errors[] = Mage::helper('vendor')->__('Name has to be completed');} 
					else{$data[$code] = $value;}
					break;
				case 'description':
					if(trim($value) == '' ){$errors[] = Mage::helper('vendor')->__('Description has to be completed');} 
					else{$data[$code] = $value;}
					break;
				case 'short_description':
					if(trim($value) == ''){$errors[] = Mage::helper('vendor')->__('Short description has to be completed');} 
					else{$data[$code] = $value;}
					break;
				case 'price':
					if(!preg_match("/^([0-9])+?[0-9.]*$/",$value)){
						$errors[] = Mage::helper('vendor')->__('Price should contain only decimal numbers');
					}else{$data[$code] = $value;}
					break;
				case 'weight':
					if(!preg_match("/^([0-9])+?[0-9.]*$/",$value)){
						$errors[] = Mage::helper('vendor')->__('Weight should contain only decimal numbers');
					}else{$data[$code] = $value;}
					break;
				case 'stock':
					if(!preg_match("/^([0-9])+?[0-9.]*$/",$value)){
						$errors[] = Mage::helper('vendor')->__('Product stock should contain only an integer number');
					}else{$data[$code] = $value;}
					break;
				case 'sku_type':
					if(trim($value) == '' ){$errors[] = Mage::helper('vendor')->__('Sku Type has to be selected');} 
					else{$data[$code] = $value;}
					break;
				case 'price_type':
					if(trim($value) == '' ){$errors[] = Mage::helper('vendor')->__('Price Type has to be selected');} 
					else{$data[$code] = $value;}
					break;
				case 'weight_type':
					if(trim($value) == ''){$errors[] = Mage::helper('vendor')->__('Weight Type has to be selected');} 
					else{$data[$code] = $value;}
					break;
				case 'bundle_options':
					if(trim($value) == ''){$errors[] = Mage::helper('vendor')->__('Default Title has to be completed');} 
					else{$data[$code] = $value;}
					break;	
			endswitch;
		}
		return array($data, $errors);
	}
	public function paymentAction(){
		$wholedata=$this->getRequest()->getParams();
		$customerid=Mage::getSingleton('customer/session')->getCustomerId();
		$collection = Mage::getModel('vendor/userprofile')->getCollection();
		$collection->addFieldToFilter('mageuserid',array('eq'=>$customerid));
		foreach($collection as $row){
			$id=$row->getAutoid();
		}
		$collectionload = Mage::getModel('vendor/userprofile')->load($id);
		$collectionload->setpaymentsource($wholedata['paymentsource']);
		$collectionload->save();
		Mage::getSingleton('core/session')->addSuccess( Mage::helper('vendor')->__('Your Payment Information Is Sucessfully Saved.'));
		$this->_redirect('vendor/vendoraccount/editProfile');
	 }
	public function askquestionAction(){
		$customerid=Mage::getSingleton('customer/session')->getCustomerId();
		$vendor = Mage::getModel('customer/customer')->load($customerid);
		$email = $vendor->getEmail();
		$name = $vendor->getFirstname()." ".$vendor->getLastname();
		$adminname = 'Administrators';
		$admin_storemail = Mage::getStoreConfig('vendor/vendor_options/adminemail');
		$adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
		$emailTemp = Mage::getModel('core/email_template')->loadDefault('queryadminemail');
		$emailTempVariables = array();
		$emailTempVariables['myvar1'] = $_POST['subject'];
		$emailTempVariables['myvar2'] =$name;
		$emailTempVariables['myvar3'] = $_POST['ask'];
		$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
		$emailTemp->setSenderName($name);
		$emailTemp->setSenderEmail($email);
		$emailTemp->send($adminEmail,'Administrators',$emailTempVariables);
	}
	public function deleteprofileimageAction(){
		$collection = Mage::getModel('vendor/userprofile')->getCollection();
		$collection->addFieldToFilter('mageuserid',array('eq'=>$this->_getSession()->getCustomerId()));
		foreach($collection as  $value){ 
			$data = $value; 
			$id = $value->getAutoid(); 
		}
		Mage::getModel('vendor/userprofile')->load($id)->setBannerpic('')->save();
		echo "true";
	}
	public function deletelogoimageAction(){
		$collection = Mage::getModel('vendor/userprofile')->getCollection();
		$collection->addFieldToFilter('mageuserid',array('eq'=>$this->_getSession()->getCustomerId()));
		foreach($collection as  $value){ 
			$data = $value; 
			$id = $value->getAutoid(); 
		}
		Mage::getModel('vendor/userprofile')->load($id)->setLogopic('')->save();
		echo "true";
	}
	public function editprofileAction(){
		if($this->getRequest()->isPost()){
			if (!$this->_validateFormKey()) {
				return $this->_redirect('vendor/vendoraccount/editProfile');
			}
			list($data, $errors) = $this->validateprofiledata();
			$fields = $this->getRequest()->getParams();
			$loid=$this->_getSession()->getCustomerId();
			$img1='';
			$img2='';
            if(empty($errors)){		
				$write = Mage::getSingleton('core/resource')->getConnection('core_write');
				$collection = Mage::getModel('vendor/userprofile')->getCollection();
				$collection->addFieldToFilter('mageuserid',array('eq'=>$this->_getSession()->getCustomerId()));
				foreach($collection as  $value){ $data = $value; }
				$value->settwitterid($fields['twitterid']);
				$value->setfacebookid($fields['facebookid']);
				$value->setcontactnumber($fields['contactnumber']);
				$value->setbackgroundth($fields['backgroundth']);
				$value->setshoptitle($fields['shoptitle']);
				$value->setcomplocality($fields['complocality']);
				$value->setMetaKeyword($fields['meta_keyword']);
				
				if($fields['compdesi']){
					$fields['compdesi'] = str_replace('script', '', $fields['compdesi']);
				}
				$value->setcompdesi($fields['compdesi']);

				if($fields['returnpolicy']){
					$fields['returnpolicy'] = str_replace('script', '', $fields['returnpolicy']);
				}
				$value->setReturnpolicy($fields['returnpolicy']);

				if($fields['shippingpolicy']){
					$fields['shippingpolicy'] = str_replace('script', '', $fields['shippingpolicy']);
				}
				$value->setShippingpolicy($fields['shippingpolicy']);

				$value->setMetaDescription($fields['meta_description']);
				if(strlen($_FILES['bannerpic']['name'])>0){
					$temp = explode(".",$_FILES["bannerpic"]["name"]);
                    $img1 = $temp[0].rand(1,99999).$loid.'.'.end($temp);
					$value->setbannerpic($img1);
				}
				if(strlen($_FILES['logopic']['name'])>0){
					$temp1 = explode(".",$_FILES["logopic"]["name"]);
                    $img2 = $temp1[0].rand(1,99999).$loid.'.'.end($temp);
					$value->setlogopic($img2);
				}
				if (array_key_exists('countrypic', $fields)) {
					$value->setcountrypic($fields['countrypic']);
				}
				$value->save();
				$target =Mage::getBaseDir().'/media/avatar/';
				$targetb = $target.$img1; 
				
				move_uploaded_file($_FILES['bannerpic']['tmp_name'],$targetb);
				$targetl = $target.$img2; 
				move_uploaded_file($_FILES['logopic']['tmp_name'],$targetl);
	           try{
					if(!empty($errors)){
		                foreach ($errors as $message){$this->_getSession()->addError($message);}
		            }else{Mage::getSingleton('core/session')->addSuccess( Mage::helper('vendor')->__('Profile information was successfully saved'));}
	                $this->_redirect('vendor/vendoraccount/editProfile');
	                return;
	            }catch (Mage_Core_Exception $e){
	                $this->_getSession()->addError($e->getMessage());
	            }catch (Exception $e){
	                $this->_getSession()->addException($e,  Mage::helper('vendor')->__('Cannot save the customer.'));
	            }
				$this->_redirect('customer/*/*');
			}else{
				foreach ($errors as $message) {Mage::getSingleton('core/session')->addError($message);}
				$_SESSION['new_products_errors'] = $data;
				$this->_redirect('vendor/vendoraccount/editProfile');
			}
        }
		else{
			$this->loadLayout( array('default','vendor_account_editaccount'));
			$this->_initLayoutMessages('customer/session');
			$this->_initLayoutMessages('catalog/session');
			$this->getLayout()->getBlock('head')->setTitle( Mage::helper('vendor')->__('Profile Information'));
			$this->renderLayout();
		}  
    }

    private function validateprofiledata(){
		$errors = array();
		$data = array();
		foreach( $this->getRequest()->getParams() as $code => $value){
			switch ($code) :
				case 'twitterid':
					if(trim($value) != '' && preg_match('/[\'^£$%&*()}{@#~?><>, |=_+¬-]/', $value)){$errors[] = Mage::helper('vendor')->__('Twitterid cannot contain space and special charecters');} 
					else{$data[$code] = $value;}
					break;
				case 'facebookid':
					if(trim($value) != '' &&  preg_match('/[\'^£$%&*()}{@#~?><>, |=_+¬-]/', $value)){$errors[] = Mage::helper('vendor')->__('Facebookid cannot contain space and special charecters');} 
					else{$data[$code] = $value;}
					break;
				case 'backgroundth':
					if(trim($value) != '' && strlen($value)!=6 && substr($value, 0, 1) != "#"){$errors[] = Mage::helper('vendor')->__('Invalid Background Color');} 
					else{$data[$code] = $value;}
					break;
			endswitch;
		}
		return array($data, $errors);
	}
	
	public function mytransactionAction(){
	   $this->loadLayout( array('default','vendor_transaction_info'));
	   $this->_initLayoutMessages('customer/session');
       $this->_initLayoutMessages('catalog/session');
	   $this->getLayout()->getBlock('head')->setTitle( Mage::helper('vendor')->__('Transactions'));
	   $this->renderLayout();  
	}

	public function viewtransdetailsAction(){
	   $this->loadLayout( array('default','vendor_vendoraccount_viewtransdetails'));
	   $this->_initLayoutMessages('customer/session');
       $this->_initLayoutMessages('catalog/session');
	   $this->getLayout()->getBlock('head')->setTitle( Mage::helper('vendor')->__('Transaction Details'));
	   $this->renderLayout();  
	}

	public function downloadtranscsvAction(){
		$id = Mage::getSingleton('customer/session')->getId();
        $transid=$this->getRequest()->getParam('transid')!=""?$this->getRequest()->getParam('transid'):"";
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
        $collection = Mage::getModel('vendor/vendortransaction')->getCollection();
        $collection->addFieldToFilter('vendorid',array('eq'=>$id));
        if($transid){
            $collection->addFieldToFilter('transactionid', array('eq' => $transid));
        }
        if($from || $to){
            $collection->addFieldToFilter('created_at', array('datetime' => true,'from' => $from,'to' =>  $to));
        }
        $collection->setOrder('transid');

        $data = array();
        foreach ($collection as $transactioncoll) {
        	$data1 =array();
        	$data1['Date'] = Mage::helper('core')->formatDate($transactioncoll->getCreatedAt(), 'medium', false);
        	$data1['Transaction Id'] = Mage::helper('core')->formatDate($transactioncoll->getCreatedAt(), 'medium', false);
        	if($transactioncoll->getCustomnote()) {
				$data1['Comment Message'] = $transactioncoll->getCustomnote(); 
			}else {
		 		$data1['Comment Message'] = Mage::helper('vendor')->__('None');
			}
        	$data1['Transaction Amount'] = Mage::helper('core')->currency($transactioncoll->getTransactionamount(), true, false);
			$data[] = $data1;
        }

	    header('Content-Type: text/csv');
	    header('Content-Disposition: attachment; filename=transactionlist.csv');
	    header('Pragma: no-cache');
	    header("Expires: 0");

	    $outstream = fopen("php://output", "w");    
	    fputcsv($outstream, array_keys($data[0]));

	    foreach($data as $result)
	    {
	        fputcsv($outstream, $result);
	    }

	    fclose($outstream);
	}
	
	public function deletelinkAction(){
		$data= $this->getRequest()->getParams();
		$_product = Mage::getModel('downloadable/link')->load($data['id'])->delete();
	}
	
	public function deletesampleAction(){
		$data= $this->getRequest()->getParams();
		$_product = Mage::getModel('downloadable/sample')->load($data['id'])->delete();
	}

	public function nicuploadscriptAction(){
		$data= $this->getRequest()->getParams();
		if(isset($_FILES['image'])){
	        $img = $_FILES['image'];
	        $imagename = rand().$img["name"];
	        $path = "nicimages/".$imagename;
	        if(!is_dir(Mage::getBaseDir().'/media/vendor/nicimages')){
				mkdir(Mage::getBaseDir().'/media/vendor/nicimages', 0755);
			}
			$target =Mage::getBaseDir().'/media/vendor/nicimages/';
			$targetpath = $target.$imagename;
	        move_uploaded_file($img['tmp_name'],$targetpath);
	        $data = getimagesize($targetpath);
	        $link = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'vendor/'.$path;
	        $res = array("upload" => array(
                        "links" => array("original" => $link),
                        "image" => array("width" => $data[0],
                                                 "height" => $data[1]
                                                )                              
                    ));
       	}
        echo json_encode($res);
	}
}
