<?php
    class Magebright_Productrestriction_Block_Adminhtml_Productrestriction_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
    {
        protected function _prepareForm()
        {
            $form = new Varien_Data_Form();
            $this->setForm($form);
            $fieldset = $form->addFieldset('Productrestriction_form', array('legend'=>Mage::helper('productrestriction')->__('Productrestriction information')));
            
            $form->setHtmlIdPrefix('productrestriction');          
              

            $fieldset->addField('pin_code', 'text', array(
                'label' => Mage::helper('productrestriction')->__('Zipcode'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'pin_code',
            )); 
            $fieldset->addField('city', 'text', array(
                'label' => Mage::helper('productrestriction')->__('City'),
                'name' => 'city',
            )); 
            
            $fieldset->addField('cod', 'select', array(
                'label'     => Mage::helper('productrestriction')->__('Cash on delivery'),
                'name'      => 'cod',
                'values'    => array(
                    array(
                        'value'     => 1,
                        'label'     => Mage::helper('productrestriction')->__('Yes'),
                    ),

                    array(
                        'value'     => 0,
                        'label'     => Mage::helper('productrestriction')->__('No'),
                    ),
                ),
            ));
            
                      
             $fieldset->addField('delivery_days', 'text', array(
                'label' => Mage::helper('productrestriction')->__('Delivery Days'),
                       'name' => 'delivery_days',
            ));  
             
          /*  if (!Mage::app()->isSingleStoreMode()) {
			    $fieldset->addField('store_id', 'multiselect', array(
			        'name' => 'stores[]',
			        'label' => Mage::helper('productrestriction')->__('Store View'),
			        'title' => Mage::helper('productrestriction')->__('Store View'),
			        'required' => true,
			        'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
			    ));
			} else {
			    $fieldset->addField('store_id', 'hidden', array(
			        'name' => 'stores[]',
			        'value' => Mage::app()->getStore(true)->getId(),
			    ));
			}
			*/
           
             
              
	     
		   
            if ( Mage::getSingleton('adminhtml/session')->getProductrestrictionData() )
            {
                $form->setValues(Mage::getSingleton('adminhtml/session')->getProductrestrictionData());
                Mage::getSingleton('adminhtml/session')->setProductrestrictionData(null);
            } elseif ( Mage::registry('productrestriction_data') ) {
                $form->setValues(Mage::registry('productrestriction_data')->getData());
            }
            return parent::_prepareForm();
        }
      
    }