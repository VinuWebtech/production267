<?php
class Magebright_Productrestriction_Block_Adminhtml_Productrestriction extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {   
        $this->_controller = 'adminhtml_productrestriction';
        $this->_blockGroup = 'productrestriction';
        $this->_headerText = Mage::helper('productrestriction')->__('Zipcode Manager');
        $this->_addButtonLabel = Mage::helper('productrestriction')->__('Add New Zipcode');
		
        parent::__construct();
        
        	

    }
}