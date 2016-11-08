<?php
    class Magebright_Productrestriction_Adminhtml_ProductrestrictionController extends Mage_Adminhtml_Controller_Action
    {
        protected function _initAction()
        {
            $this->loadLayout()
                ->_setActiveMenu('productrestriction/items')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Zipcodeck Manager'), Mage::helper('adminhtml')->__('Zipcodeck Manager'));
            return $this;
        }   
        public function indexAction() {
        $this->_initAction();
        // $this->_addContent($this->getLayout()->createBlock('productrestriction/adminhtml_productrestriction'));
         $this->renderLayout();
        }
         public function newAction() {
            $this->_forward('edit');
        }
        public function editAction()
        {
             $this->_registryObject();
            $attributeInfo = Mage::getResourceModel('eav/entity_attribute_collection')
                            ->setCodeFilter('productrestriction')->getFirstItem();
            $collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                    ->setPositionOrder('asc')
                    ->setAttributeFilter($attributeInfo->getAttributeId())
                    ->setStoreFilter(Mage::app()->getStore()->getId());
            $id = $this->getRequest()->getParam('id');
            $model = Mage::getModel('productrestriction/productrestriction')->load($id);

            if ($model->getProductrestrictionOptionId()) {
                $data1 = $collection->getItemByColumnValue("option_id", $id);
                $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
                if (!empty($data)) {
                    $model->setData($data);
                }
                $model->setProductrestrictionName($data1['value']);
                $path = $model->getImage();

                if(!empty($path)) {
                    $model->setImage(Mage::getBaseUrl('media')."productrestriction/".$path);
                }
                Mage::register('productrestriction_data', $model);

                $this->loadLayout();
                $this->_setActiveMenu('productrestriction/items');

                $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Zipcodeck Names Manager'), Mage::helper('adminhtml')->__('Zipcodeck Names Manager'));
                $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Zipcodeck Name'), Mage::helper('adminhtml')->__('Zipcodeck Name'));

                $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

                $this->_addContent($this->getLayout()->createBlock('productrestriction/adminhtml_productrestriction_edit'))
                        ->_addLeft($this->getLayout()->createBlock('productrestriction/adminhtml_productrestriction_edit_tabs'));

                $this->renderLayout();
            } else {

                $data = $collection->getItemByColumnValue("option_id", $id);

                if ($data) {
                    $data->getData();
                   // $model->setTitle($data['value']);
                    $model->setProductrestrictionName($data['value']);
                    //$model->setName($data['value']);
                    //$model->setProductrestrictionAttributeId($data['option_id']);
                    //$model->getProductrestrictionAttributeId($data['option_id']);
                }
                $path = $model->getImage();

                if(!empty($path)) {
                    $model->setImage(Mage::getBaseUrl('media')."productrestriction/".$path);
                }

                Mage::register('productrestriction_data', $model);

                $this->loadLayout();
                $this->_setActiveMenu('productrestriction/items');

                $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Zipcodeck Name Manager'), Mage::helper('adminhtml')->__('Zipcodeck Names Manager'));
                $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Zipcodeck Name'), Mage::helper('adminhtml')->__('Zipcodeck Name'));

                $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

                $this->_addContent($this->getLayout()->createBlock('productrestriction/adminhtml_productrestriction_edit'))
                        ->_addLeft($this->getLayout()->createBlock('productrestriction/adminhtml_productrestriction_edit_tabs'));

                $this->renderLayout();
            }
        }





    public function saveAction() {

        if ($data = $this->getRequest()->getPost()) {


        /*  if( isset($data['stores']) ) {
                if( in_array('0', $data['stores']) ){
                    $data['store_id'] = '0';
                } else {
                    $data['store_id'] = join(",", $data['stores']);
                }
                unset($data['stores']);
            }
            */
                $isExist=0;


                $model = Mage::getModel('productrestriction/productrestriction');
                $id = $this->getRequest()->getParam('id');
                if(isset($id)){

                   $editdata=Mage::getModel('productrestriction/productrestriction')->load($id);
                   $existzipcode=$editdata->getPinCode();
                  if($existzipcode!=$data['pin_code']) {
                    $collection = $model->getCollection();
                    $collection->addFieldToFilter('pin_code',$data['pin_code']);
                    $collection->getSelect()->limit(1);
                    if($collection->getSize()>0)
                        {    $isExist=1;
                            Mage::getSingleton('adminhtml/session')->addError('Zipcode Allready Exist.');
                        }else{
                            $model->setData($data)->setId($this->getRequest()->getParam('id'));
                            $model->save();
                        }    
                  }else{
                     $model->setData($data)->setId($this->getRequest()->getParam('id'));
                      $model->save();
                    }


                }else
                {
                    $collection = $model->getCollection();
                    $collection->addFieldToFilter('pin_code',$data['pin_code']);
                    $collection->getSelect()->limit(1);
                    if($collection->getSize()>0)
                        {    $isExist=1;
                              Mage::getSingleton('adminhtml/session')->addError('Zipcode Allready Exist.');
                        }else{
                                $model->setData($data);
                                $model->save();
                        }
                 }
            try {
                   // var_dump($model);die;

              if(isset($data['selected_products']) && $isExist==0){
                $model->setProductId('');
                $products = Mage::helper('adminhtml/js')->decodeGridSerializedInput($data['selected_products']); //Save the array to your database

                $model->save();
                $collection_del= $product_collection =Mage::getModel('productrestriction/zipcodeproduct')->getCollection()->addFieldToFilter('pin_code',array('eq'=>$data['pin_code']));
                $collection_del->walk('delete');
                 foreach($products as $pid=>$product){
                  // echo $pid;
                         //print_r($product);
                         Mage::getModel('productrestriction/zipcodeproduct')
                    ->setPinCode($data['pin_code'])
                    -> setProductId($pid)
                    ->save();
                   }
               // print_r($products);
               // exit;

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('productrestriction')->__('Zipcodeck was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getProductrestrictionId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
               }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('productrestriction_id')));
                return;
            }
        }
        //Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productrestriction')->__('Unable to find zipcodeck to save'));
        $this->_redirect('*/*/');

    }
        protected function _getHelper()
    {
        return Mage::helper('adminhtml/catalog_product_edit_action_attribute');
    }
        public function deleteAction()
        {

                  if ($this->getRequest()->getParam('productrestriction_id') > 0) {
                        $model = Mage::getModel('productrestriction/productrestriction')->load($this->getRequest()->getParam('productrestriction_id'), 'productrestriction_id');
                        Mage::helper('productrestriction')->deleteProductZipcode($model->getPinCode());
                        try {



                            if ($model->getProductrestrictionId()) {
                                $model->delete();
                            }
                        } catch (Exception $e) {
                            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Zipcodeck could not be deleted'));
                            $this->_redirect('*/*/');
                        }
                  }
        $this->_redirect('*/*/');
        }
        public function massDeleteAction() {
            $productrestrictionIds = $this->getRequest()->getParam('productrestriction');



            $resource = Mage::getSingleton('core/resource');
            $writeConnection = $resource->getConnection('core_write');
            $tablename= $resource->getTableName('productrestriction');
            $product_zipcode_table= $resource->getTableName('zipcodeproduct');

            if (!is_array($productrestrictionIds)) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select zipcodeck(s)'));
            } else {

                try {

                    //Delete record from zipcodeproduct table:
                    $sel_zipcode = "select pin_code from  ".$tablename." where productrestriction_id in (".implode(',',$productrestrictionIds).")";
                    $final_del_query = "delete from ".$product_zipcode_table." where pin_code in (".$sel_zipcode.")";
                    $writeConnection->query($final_del_query);

                    $final_query = "delete from ".$tablename." where productrestriction_id in (".implode(',',$productrestrictionIds).")";
                    $writeConnection->query($final_query);




                    Mage::getSingleton('adminhtml/session')->addSuccess(
                            Mage::helper('adminhtml')->__(
                                    'Total of %d record(s) were successfully deleted', count($productrestrictionIds)
                            )
                    );
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            }
            $this->_redirect('*/*/index');
        }
         public function exportCsvAction() {
            $fileName = 'productrestriction.csv';
            $content = $this->getLayout()->createBlock('productrestriction/adminhtml_productrestriction_grid')
                    ->getCsv();

            $this->_sendUploadResponse($fileName, $content);
        }

        public function exportXmlAction() {
            $fileName = 'productrestriction.xml';
            $content = $this->getLayout()->createBlock('productrestriction/adminhtml_productrestriction_grid')
                    ->getXml();

            $this->_sendUploadResponse($fileName, $content);
        }

        protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream') {
            $response = $this->getResponse();
            $response->setHeader('HTTP/1.1 200 OK', '');
            $response->setHeader('Pragma', 'public', true);
            $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
            $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
            $response->setHeader('Last-Modified', date('r'));
            $response->setHeader('Accept-Ranges', 'bytes');
            $response->setHeader('Content-Length', strlen($content));
            $response->setHeader('Content-type', $contentType);
            $response->setBody($content);
            $response->sendResponse();
            die;
        }
        /**
         * Product grid for AJAX request.
         * Sort and filter result for example.
         */
        public function gridAction()
        {
            $this->loadLayout();
            $this->getResponse()->setBody(
                   $this->getLayout()->createBlock('productrestriction/adminhtml_productrestriction_grid')->toHtml()
            );
        }
        
        
        public function productAction(){

            $this->_registryObject();    
            $this->loadLayout();
            $this->getLayout()->getBlock('product.grid')->setProducts($this->getRequest()->getPost('products', null));
            $this->renderLayout();
        }
        public function productgridAction(){

            $this->_registryObject();        
            $this->loadLayout(); 
            $this->getLayout()->getBlock('product.grid')->setProducts($this->getRequest()->getPost('products', null));

            $this->renderLayout();
        }
        /**
        * registry form object
        */
        protected function _registryObject()
        { 
            if($id  = $this->getRequest()->getParam('id')) {
                $model  = Mage::getModel('productrestriction/productrestriction')->load($id);  
                Mage::register('productrestriction', $model);  
            }
        }
        
        public function exportAction()
        {
        try {
            $website = $this->getRequest()->getParam('website');
            $store = $this->getRequest()->getParam('store');
            $stores = array();
            if($store) {
                $stores[] = $store;
            } else if ($website) {
                $stores = Mage::app()->getWebsite($website)->getStoreCodes();
            }
            /* @var $collection Unirgy_StoreLocator_Model_Mysql4_Productrestriction_Collection */
            $collection = Mage::getModel('productrestriction/productrestriction')->getCollection();
            
            
            $data = $collection->getData(); 
         
            if (!empty($data)) {
                $target = Mage::getConfig()->getVarDir('productrestriction/export');
                Mage::getConfig()->createDirIfNotExists($target);
                $filename = 'export.csv';
                $path = $target . DS .$filename;
                $fh = @fopen($path, 'w');
                if (!$fh) {
                    Mage::throwException(Mage::helper('productrestriction')->__("Could not open %s for writing.", $path));
                }
                $headers = false;
                foreach($data as $line) {
                    if(isset($line['productrestriction_id'])) {
                        unset($line['productrestriction_id']);
                    }
                    
                     if(isset($line['product_id'])) {
                       
                        $line['product_id']=str_replace(',','-',$line['product_id']);
                    }
                    
                    if($headers === false) {
                        $headers = array_keys($line);
                        fputcsv($fh, $headers);
                    }
                    fputcsv($fh, $line);
                }
                fclose($fh);
                return $this->_prepareDownloadResponse($filename, file_get_contents($path), 'text/csv');
            } else {
                $this->_getSession()->addWarning(Mage::helper('productrestriction')->__("No zipcodeck found."));
                $this->_redirect('*/*/');
            }
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/'); // redirect to previous page
        }
    }
     public function exportproductzipcodeAction()
        {
        try {
            $website = $this->getRequest()->getParam('website');
            $store = $this->getRequest()->getParam('store');
            $stores = array();
            if($store) {
                $stores[] = $store;
            } else if ($website) {
                $stores = Mage::app()->getWebsite($website)->getStoreCodes();
            }
            /* @var $collection Unirgy_StoreLocator_Model_Mysql4_Productrestriction_Collection */
            $collection = Mage::getModel('productrestriction/zipcodeproduct')->getCollection();


            $data = $collection->getData();

            if (!empty($data)) {
                $target = Mage::getConfig()->getVarDir('productrestriction/export');
                Mage::getConfig()->createDirIfNotExists($target);
                $filename = 'product_zipcode.csv';
                $path = $target . DS .$filename;
                $fh = @fopen($path, 'w');
                if (!$fh) {
                    Mage::throwException(Mage::helper('productrestriction')->__("Could not open %s for writing.", $path));
                }
                $headers = false;
                foreach($data as $line) {
                    if(isset($line['zipcodeproduct_id'])) {
                        unset($line['zipcodeproduct_id']);
                    }

                    /* if(isset($line['product_id'])) {

                        $line['product_id']=str_replace(',','-',$line['product_id']);
                    } */

                    if($headers === false) {
                        $headers = array_keys($line);
                        fputcsv($fh, $headers);
                    }
                    fputcsv($fh, $line);
                }
                fclose($fh);
                return $this->_prepareDownloadResponse($filename, file_get_contents($path), 'text/csv');
            } else {
                $this->_getSession()->addWarning(Mage::helper('productrestriction')->__("No zipcodeck found."));
                $this->_redirect('*/*/');
            }
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/'); // redirect to previous page
        }
    }
  }