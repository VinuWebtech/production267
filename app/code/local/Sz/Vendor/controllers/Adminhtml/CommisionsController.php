<?php
class Sz_Vendor_Adminhtml_CommisionsController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction() {
		$this->_title($this->__("Manage Vendor's Commission"));
		$this->loadLayout()
			->_setActiveMenu('vendor/vendor_commisions')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}   
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}
	public function payamountAction(){
	    $data=$this->getRequest();
		Mage::getModel('vendor/saleperpartner')->salePayment($data);
		
		$this->_redirectReferer();	
	}
	public function masspayamountAction(){
	    $data=$this->getRequest();
		Mage::getModel('vendor/saleperpartner')->masssalePayment($data);
		
		$this->_redirectReferer();	
	}
	public function gridAction(){
            $this->loadLayout();
            $this->getResponse()->setBody($this->getLayout()->createBlock("vendor/adminhtml_commisions_grid")->toHtml()); 
    }
}