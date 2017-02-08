<?php
/**
* 
*/
class Buildmatic_Brands_Adminhtml_BrandsController extends Mage_Adminhtml_Controller_action
{
	
	public function indexAction()
	{
		$this->loadLayout();
		$this->renderLayout();
	}

	public function deleteAction()
    {
		if( $this->getRequest()->getParam('id') > 0)
		{
			try{
				$model=Mage::getModel('buildmatic_brands/brands');
				$model->setId($this->getRequest()->getParam('id'))->delete();                   
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
                $this->_redirect('*/*/');
			}catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id'))
                );
            }
		}
		$this->_redirect('*/*/');
	}

	public function saveAction()
    {
        if ($id = $this->getRequest()->getParam('id')) 
        {
            $data = $this->getRequest()->getPost();
            $model = Mage::getModel('buildmatic_brands/brands')->load('id');
            if(isset($_FILES['brand_image']['name']) and (file_exists($_FILES['brand_image']['tmp_name']))) 
            {
              try 
              {
                $uploader = new Varien_File_Uploader('brand_image');
                $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png')); // or pdf or anything              

                $uploader->setAllowRenameFiles(false);              

                // setAllowRenameFiles(true) -> move your file in a folder the magento way
                // setAllowRenameFiles(true) -> move your file directly in the $path folder

                $uploader->setFilesDispersion(false);
                $path = Mage::getBaseDir('media').DS;
                $uploader->save($path, $_FILES['brand_image']['name']);
                $data['brand_image'] = $_FILES['brand_image']['name'];
              }
              catch(Exception $e) 
              {             

              }
            } 
            else 
            {      
                if(isset($data['brand_image']['delete']) && $data['brand_image']['delete'] == 1)
                    $data['brand_image'] = '';
                else
                    //$data['image'] = $data['image']['value'];
                    unset($data['brand_image']);
            }
            $model->setData($data);
            $model->setId($id)->save();
        }else{
            $data = $this->getRequest()->getPost();
            $model = Mage::getModel('buildmatic_brands/brands');
            if(isset($_FILES['brand_image']['name']) and (file_exists($_FILES['brand_image']['tmp_name']))) 
            {
              try 
              {
                $uploader = new Varien_File_Uploader('brand_image');
                $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png')); // or pdf or anything
                $uploader->setAllowRenameFiles(false);            

                // setAllowRenameFiles(true) -> move your file in a folder the magento way
                // setAllowRenameFiles(true) -> move your file directly in the $path folder

                $uploader->setFilesDispersion(false);
                $path = Mage::getBaseDir('media').DS;
                $uploader->save($path, $_FILES['brand_image']['name']);
                $data['brand_image'] = $_FILES['brand_image']['name'];
              }
              catch(Exception $e) 
              {             

              }
            } 
            else 
            {      
                if(isset($data['brand_image']['delete']) && $data['brand_image']['delete'] == 1)
                    $data['brand_image'] = '';
                else
                    //$data['image'] = $data['image']['value'];
                    unset($data['brand_image']);
            }
            $model->setData($data);
            $model->save();
        }
        try
        {
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The brand has been saved.'));
            $this->_redirect('*/*/');
            return;
        }  
        catch (Mage_Core_Exception $e) 
        {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        catch (Exception $e) 
        {
            Mage::getSingleton('adminhtml/session')->addError($this->__('An error occurred while saving this enquiry.'));
        }
        Mage::getSingleton('adminhtml/session')->setEnquiryData($postData);
        $this->_redirectReferer();
    }

	public function newAction()
    {
        $this->_forward('edit');
	}

	public function editAction()
	{
		if($id = $this->getRequest()->getParam('id'))
        {
            $model = Mage::getModel('buildmatic_brands/brands')->load($id);
            Mage::register('brand_data', $model);
        }

		$this->loadLayout();
		$this->_addContent($this->getLayout()->createBlock('buildmatic_brands/adminhtml_brands_edit'))->_addLeft($this->getLayout()->createBlock('buildmatic_brands/adminhtml_brands_edit_tabs'));
		$this->renderLayout();
	}
}