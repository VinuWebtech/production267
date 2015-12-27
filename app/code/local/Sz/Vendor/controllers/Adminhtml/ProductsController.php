<?php
class Sz_Vendor_Adminhtml_ProductsController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction() {
		$this->_title($this->__("Manage Vendor's Products"));
		$this->loadLayout()
			->_setActiveMenu('vendor/vendor_products')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

    public function approveproductsAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function processcsvAction() {
        $this->_title($this->__("Process Product CSV"));
        $this->loadLayout()
            ->_setActiveMenu('vendor/vendor_process_products')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
        $this->renderLayout();
    }

    public function processAction(){
        $fileIds = $this->getRequest()->getParam('file_id');
        if (!is_null($fileIds)) {
            $uploader = Mage::getModel('vendor/uploader');
            foreach ($fileIds as $fileId) {
                $fileData = $uploader->load($fileId);
                if ($fileData->getFileName() &&
                    ($fileData->getStatus() != Sz_Vendor_Model_Uploader::COMPLETE)) {
                    $fileData->setStatus(Sz_Vendor_Model_Uploader::WORKING);
                    $fileData->save();
                    try {
                        if ($fileData->getFileType() == Sz_Vendor_Model_Uploader::PRODUCT_IMAGE) {
                            $status = $uploader->importProductImages($fileData->getFileName(), $fileData);
                        } else {
                            $status = $uploader->importProduts($fileData->getFileName(), $fileData);
                        }

                        if ($status) {
                            $fileData->setStatus(Sz_Vendor_Model_Uploader::COMPLETE);
                            $fileData->save();
                        }
                    } catch (Exception $e) {
                        $fileData->setStatus(Sz_Vendor_Model_Uploader::ERROR);
                        $fileData->save();
                        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    }

                }
            }
            $this->_redirectReferer(true);
        }
    }
	public function denyAction(){
		$wholedata=$this->getRequest()->getParams();
		$productid = $wholedata['productid'];
		$vendorid = $wholedata['vendorid'];
		$collection = Mage::getModel('vendor/product')->getCollection()
							->addFieldToFilter('mageproductid',array('eq'=>$productid))
							->addFieldToFilter('userid',array('eq'=>$vendorid));
		foreach ($collection as $row) {
			$id = $row->getMageproductid();
			$magentoProductModel = Mage::getModel('catalog/product')->load($id);
			$catarray=$magentoProductModel->getCategoryIds();
			$categoryname='';
			$catagory_model = Mage::getModel('catalog/category');
			foreach($catarray as $keycat){
			$categoriesy = $catagory_model->load($keycat);
				if($categoryname ==''){
					$categoryname=$categoriesy->getName();
				}else{
					$categoryname=$categoryname.",".$categoriesy->getName();
				}
			}
			$allStores = Mage::app()->getStores();
			foreach ($allStores as $_eachStoreId => $val)
			{
				Mage::getModel('catalog/product_status')->updateProductStatus($id,Mage::app()->getStore($_eachStoreId)->getId(), Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
			}
			$magentoProductModel->setStatus(2)->save();
			$row->setStatus(2);
			$row->save();
		}
		$customer = Mage::getModel('customer/customer')->load($vendorid);	
		$emailTemp = Mage::getModel('core/email_template')->loadDefault('productdeny');
		$emailTempVariables = array();
		$admin_storemail = Mage::getStoreConfig('vendor/vendor_options/adminemail');
		$adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
		$adminUsername = 'Admin';
		$emailTempVariables['myvar1'] = $customer->getName();
		$emailTempVariables['myvar2'] = $wholedata['product_deny_reason'];
		$emailTempVariables['myvar3'] = $magentoProductModel->getName();
		$emailTempVariables['myvar4'] = $categoryname;
		$emailTempVariables['myvar5'] = $magentoProductModel->getDescription();
		$emailTempVariables['myvar6'] = $magentoProductModel->getPrice();
		
		$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
		
		$emailTemp->setSenderName($adminUsername);
		$emailTemp->setSenderEmail($adminEmail);
		$emailTemp->send($customer->getEmail(),$adminUsername,$emailTempVariables);
		$vendorname = $customer->getName();
		$this->_getSession()->addSuccess($magentoProductModel->getName().Mage::helper('vendor')->__(' has been successfully denied'));

		$this->_redirect('vendor/adminhtml_products/');
	}
	public function approveAction(){
		$id = (int)$this->getRequest()->getParam('id');
		if(!$id){$this->_redirectReferer();}
		$lastId=Mage::getModel('vendor/product')->approveSimpleProduct($id);
		Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('vendor')->__('Product successfully approved.'));
		$this->_redirect('adminhtml/catalog_product/edit', array('id' => $lastId,'_current'=>true));
		
	}
	public function massapproveAction(){
		$ids = $this->getRequest()->getParam('vendorproduct');
        $vendorId = $this->getRequest()->getParam('vendor_id', 0);
        if(!is_array($ids)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                	$vendorproduct = Mage::getModel('vendor/product')->load($id);
					Mage::getModel('vendor/product')->approveSimpleProduct($vendorproduct);
				}
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully aprroved', count($ids)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
		}
        if ($vendorId) {
            $this->_redirect('*/*/index', array('vendor_id'=>$vendorId));
            return;
        } else {
            $this->_redirect('*/*/index');
            return;
        }

	}
	public function gridAction(){
            $this->loadLayout();
            $this->getResponse()->setBody($this->getLayout()->createBlock('vendor/adminhtml_products_grid')->toHtml()); 
        }
    public function processcsvgridAction(){
        $this->loadLayout();
        $this->getResponse()->setBody($this->getLayout()->createBlock('vendor/adminhtml_uploader_grid')->toHtml());
    }
}
