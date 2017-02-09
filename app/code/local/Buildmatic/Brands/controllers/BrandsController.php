<?php
class Buildmatic_Brands_BrandsController extends Mage_Core_Controller_Front_Action
{
	public function viewAction()
    {   
        $this->loadLayout();
        $this->renderLayout();
    }
}