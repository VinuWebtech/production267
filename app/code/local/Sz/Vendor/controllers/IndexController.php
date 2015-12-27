<?php
class Sz_Vendor_IndexController extends Mage_Core_Controller_Front_Action
{
     public function indexAction(){
		$vendorlabel=Mage::getStoreConfig('vendor/vendor_options/vendorlabel');
		$this->loadLayout(array('default','vendor_index_toplinkvendor'));
		$this->getLayout()->getBlock('head')->setTitle( Mage::helper('vendor')->__($vendorlabel));
		$this->renderLayout();
    }
	public function toplinkvendorAction(){
		$this->loadLayout(); 
		$this->renderLayout();
	}
}