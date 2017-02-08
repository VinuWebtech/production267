<?php
class Buildmatic_Brands_Block_Adminhtml_Brands_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                  
        $this->_objectId = 'id';
        $this->_blockGroup = 'buildmatic_brands';
        $this->_controller = 'adminhtml_brands';
         
        $this->_updateButton('save', 'label', Mage::helper('buildmatic_brands')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('buildmatic_brands')->__('Delete'));
         
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('buildmatic_brands')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }";
    }
 
    public function getHeaderText()
    {
        return Mage::helper('buildmatic_brands')->__('Brands Container');
    }

    protected function _prepareLayout() 
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) 
        {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }   
}