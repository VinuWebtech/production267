<?php
class Buildmatic_Brands_Block_Adminhtml_Brands_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
 
  public function __construct()
  {
    parent::__construct();
    $this->setId('edit_tabs');
    $this->setDestElementId('edit_form'); // this should be same as the form id define above
    $this->setTitle(Mage::helper('buildmatic_brands')->__('Brands'));
  }
 
  protected function _beforeToHtml()
  {
    $this->addTab('form_section', array(
      'label'     => Mage::helper('buildmatic_brands')->__('Brands Information'),
      'title'     => Mage::helper('buildmatic_brands')->__('Brands Information'),
      'content'   => $this->getLayout()->createBlock('buildmatic_brands/adminhtml_brands_edit_tab_form')->toHtml(),
    ));
    
    return parent::_beforeToHtml();
  }
}