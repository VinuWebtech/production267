<?php

class ITwebexperts_Request4quote_Adminhtml_Quote_StatusController extends Mage_Adminhtml_Controller_Action {

    protected function _isAllowed(){
        return Mage::getSingleton('admin/session')->isAllowed('admin/request4quote/quote_statuses');
    }

	protected function _initStatus()
    {
        $statusCode = $this->getRequest()->getParam('status');
        if ($statusCode) {
            $status = Mage::getModel('request4quote/quote_status')->load($statusCode);
        } else {
            $status = false;
        }
        return $status;
    }
	
	public function indexAction()
    {
        $this->_title($this->__('Request4Quote'))->_title($this->__('Quote Statuses'));
        $this->loadLayout()->_setActiveMenu('request4quote')->renderLayout();
    }
	
	public function newAction()
	{
		$data = $this->_getSession()->getFormData(true);
		if ($data) {
            $status = Mage::getModel('request4quote/quote_status')
                ->setData($data);
            Mage::register('current_status', $status);
        }
		$this->_title($this->__('Request4Quote'))->_title($this->__('Create New Quote Status'));
        $this->loadLayout()
            ->renderLayout();
	}
	
	public function editAction()
    {
        $status = $this->_initStatus();
        if ($status) {
            Mage::register('current_status', $status);
            $this->_title($this->__('Request4Quote'))->_title($this->__('Edit Quote Status'));
            $this->loadLayout()
                ->renderLayout();
        } else {
            $this->_getSession()->addError(
                Mage::helper('request4quote')->__('Quote status does not exist.')
            );
            $this->_redirect('*/');
        }
    }
	
	public function removeAction()
	{
		$statusCode = $this->getRequest()->getParam('status');
		if ($statusCode) {
			$status = Mage::getModel('request4quote/quote_status')
                ->load($statusCode);
			if ($status->getIsSystem()) {
				$this->_getSession()->addError(
                    Mage::helper('request4quote')->__('Quote status is system.')
                );
                $this->_redirect('*/*/');
                return;
			}
			try {
				$status->delete();
				$this->_getSession()->addSuccess(Mage::helper('request4quote')->__('The quote status has been removed.'));
			} catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException(
                    $e,
                    Mage::helper('request4quote')->__('An error occurred while removing quote status. The status has not been added.')
                );
            }
		}
		$this->_redirect('*/*/');
	}
	
	public function saveAction()
    {
        $data = $this->getRequest()->getPost();
        $isNew = $this->getRequest()->getParam('is_new');
        if ($data) {

            $statusCode = $this->getRequest()->getParam('status');

            //filter tags in labels/status
            /** @var $helper Mage_Adminhtml_Helper_Data */
            $helper = Mage::helper('adminhtml');
            if ($isNew) {
                $statusCode = $data['status'] = $helper->stripTags($data['status']);
            }
            $data['label'] = $helper->stripTags($data['label']);
            foreach ($data['store_labels'] as &$label) {
                $label = $helper->stripTags($label);
            }
			$data['is_new'] = 0;

            $status = Mage::getModel('request4quote/quote_status')
                    ->load($statusCode);
					
			
            // check if status exist
            if ($isNew && $status->getStatus()) {
                $this->_getSession()->addError(
                    Mage::helper('request4quote')->__('Quote status with the same status code already exist.')
                );
                $this->_getSession()->setFormData($data);
                $this->_redirect('*/*/new');
                return;
            } else if (!$isNew && $status->getStatus()) {
				$originalData = $status->getData();
				$originalData['label'] = $data['label'];
				$originalData['store_labels'] = $data['store_labels'];
				$data = $originalData;
			}
			
			/*if ($status->getIsSystem()) {
				$this->_getSession()->addError(
                    Mage::helper('request4quote')->__('Quote status is system.')
                );
                $this->_getSession()->setFormData($data);
                $this->_redirect('new');
                return;
			}*/

            $status->setData($data)
                    ->setStatus($statusCode);
            try {
                $status->save();
                $this->_getSession()->addSuccess(Mage::helper('request4quote')->__('The quote status has been saved.'));
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException(
                    $e,
                    Mage::helper('request4quote')->__('An error occurred while saving quote status. The status has not been added.')
                );
            }
            $this->_getSession()->setFormData($data);
            if ($isNew) {
                $this->_redirect('*/*/new');
            } else {
                $this->_redirect('*/*/edit', array('status' => $this->getRequest()->getParam('status')));
            }
            return;
        }
        $this->_redirect('*/*/');
    }
	
}