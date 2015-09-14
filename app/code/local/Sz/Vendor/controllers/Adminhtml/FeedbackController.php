<?php
class Sz_Vendor_Adminhtml_FeedbackController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction() {
		$this->_title($this->__("Manage Vendor's Feedbacks"));
		$this->loadLayout()
			->_setActiveMenu('vendor/vendor_feedback')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}
	public function approveAction(){
		$feedids= $this->getRequest()->getParam('feedid');
		foreach($feedids as $key){
			$feedback = Mage::getModel('vendor/feedback')->load($key);
			$feedback->setStatus(1);
			$feedback->save();
		}	
		Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d Feedback(s) were successfully approved', count($feedids))
                );
		$this->_redirect('vendor/adminhtml_feedback/');
	}
	public function unapproveAction(){
		$feedids= $this->getRequest()->getParam('feedid');
		foreach($feedids as $key){
			$feedback = Mage::getModel('vendor/feedback')->load($key);
			$feedback->setStatus(0);
			$feedback->save();
		}	
		Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d Feedback(s) were successfully unapproved', count($feedids))
                );
		$this->_redirect('vendor/adminhtml_feedback/');
	}
	
	public function massdeleteAction(){
		$feedids= $this->getRequest()->getParam('feedid');
		foreach($feedids as $key){
			$feedback = Mage::getModel('vendor/feedback')->load($key);
			$feedback->delete();
		}	
		Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d Feedback(s) were successfully deleted', count($feedids))
                );
		$this->_redirect('vendor/adminhtml_feedback/');
	}
	public function gridAction(){
            $this->loadLayout();
            $this->getResponse()->setBody($this->getLayout()->createBlock("vendor/adminhtml_feedback_grid")->toHtml()); 
        }
}