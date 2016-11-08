<?php
    class Magebright_Productrestriction_Block_Adminhtml_Productrestriction_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
    {
        protected function _prepareLayout()
        {
            // Load Wysiwyg on demand and Prepare layout
            if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled() && ($block = $this->getLayout()->getBlock('head'))) {
            $block->setCanLoadTinyMce(true);
            }
            parent::_prepareLayout();
        }
        
        public function __construct()
        {
            parent::__construct();
                   
            $this->_objectId = 'id';
            $this->_blockGroup = 'productrestriction';
            $this->_controller = 'adminhtml_productrestriction';
     
            $this->_updateButton('save', 'label', Mage::helper('productrestriction')->__('Save Zipcode'));
            $this->_updateButton('delete', 'label', Mage::helper('productrestriction')->__('Delete Zipcode'));
			$this->_updateButton('delete', 'onclick', 'deleteConfirm(\'Are you sure you want to do this?\', \'' .$this->getUrl('productrestriction/adminhtml_productrestriction/delete/productrestriction_id/', array('productrestriction_id' => $this->getRequest()->getParam('id'))).'\')');
			
			$this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('productrestriction')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
            ), -100);

            $this->_formScripts[] = "function saveAndContinueEdit()" .
            "{editForm.submit($('edit_form').action + 'back/edit/')}";
			
        }
     
        public function getHeaderText()
        {
            if( Mage::registry('productrestriction_data') && Mage::registry('productrestriction_data')->getId() ) {
                     
                return Mage::helper('productrestriction')->__("Edit Zipcode '%s'", $this->htmlEscape(Mage::registry('productrestriction_data')->getPinCode()));
            } else {
                return Mage::helper('productrestriction')->__('Add Zipcode');
            }
        }
    }