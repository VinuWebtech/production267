<?php
class Buildmatic_Brands_Block_Adminhtml_Brands_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('brands_form', array('legend'=>Mage::helper('buildmatic_brands')->__('Brand information')));
        
      $fieldset->addField('brand_name', 'text', array(
        'label'     => Mage::helper('buildmatic_brands')->__('Brand Name'),
        'class'     => 'required-entry',
        'required'  => true,
        'style'     => 'width:700px',
        'name'      => 'brand_name'
      ));

      $fieldset->addField('meta_title', 'text', array(
        'label'     => Mage::helper('buildmatic_brands')->__('Meta Title'),
        'class'     => 'required-entry',
        'required'  => true,
        'style'     => 'width:700px',
        'name'      => 'meta_title'
      ));

      $fieldset->addField('meta_description', 'textarea', array(
        'label'     => Mage::helper('buildmatic_brands')->__('Meta Description'),
        'class'     => 'required-entry',
        'required'  => true,
        'style'     => 'width:700px',
        'name'      => 'meta_description'
      ));

      $fieldset->addField('meta_keyword', 'textarea', array(
        'label'     => Mage::helper('buildmatic_brands')->__('Meta Keywords'),
        'class'     => 'required-entry',
        'required'  => true,
        'style'     => 'width:700px',
        'name'      => 'meta_keyword'
      ));

      $fieldset->addField('description', 'editor', array(
        'label'     => Mage::helper('buildmatic_brands')->__('Description'),
        'class'     => 'required-entry',
        'required'  => true,
        'style'     => 'width:700px',
        'name'      => 'description',
        'config'    => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),
       // 'wysiwyg' => true// enable WYSIWYG editor
      ));

      $fieldset->addField('url_key', 'text', array(
        'label'     => Mage::helper('buildmatic_brands')->__('Url Key'),
        'class'     => 'required-entry',
        'required'  => true,
        'style'     => 'width:700px',
        'name'      => 'url_key'
      ));
      
      $fieldset->addField('brand_image', 'image', array(
        'label'     => Mage::helper('buildmatic_brands')->__('Brand Image'),
        'value'     => '',
        'name'      => 'brand_image',
        'required'  => false
      ));

      if (Mage::registry('brand_data')) {
          $form->setValues(Mage::registry('brand_data')->getData());
      }

      return parent::_prepareForm();
    }
}