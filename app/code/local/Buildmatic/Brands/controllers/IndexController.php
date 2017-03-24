<?php
class Buildmatic_Brands_IndexController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
    {   
    	//echo "<pre>"; print_r(Mage::app()->getRequest());
        $this->loadLayout();
        $this->renderLayout();
    }
}