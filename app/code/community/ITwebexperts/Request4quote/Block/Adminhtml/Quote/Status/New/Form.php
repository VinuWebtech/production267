<?php
class ITwebexperts_Request4quote_Block_Adminhtml_Quote_Status_New_Form extends Mage_Adminhtml_Block_Widget_Form {
	
	public function __construct()
    {
        parent::__construct();
        $this->setId('new_quote_status');
    }
	
	protected function _prepareForm()
    {
        $model  = Mage::registry('current_status');
        $labels = $model ? $model->getStoreLabels() : array();

        $form   = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));

        $fieldset   = $form->addFieldset('base_fieldset', array(
            'legend'    => Mage::helper('sales')->__('Quote Status Information')
        ));

        $fieldset->addField('is_new', 'hidden', array('name' => 'is_new', 'value' => 1));

        $fieldset->addField('status', 'text',
            array(
                'name'      => 'status',
                'label'     => Mage::helper('request4quote')->__('Status Code'),
                'class'     => 'required-entry validate-code',
                'required'  => true,
            )
        );

        $fieldset->addField('label', 'text',
            array(
                'name'      => 'label',
                'label'     => Mage::helper('request4quote')->__('Status Label'),
                'class'     => 'required-entry',
                'required'  => true,
            )
        );

        $checked = (is_object($model)) ? $model->getAllowviewcheckout() : false;

        $fieldset->addField('allowviewcheckout', 'checkbox',
            array(
                'name'      => 'allowviewcheckout',
                'label'     => Mage::helper('request4quote')->__('Allow customer to view price and add to cart?'),
                'checked'     =>  $checked
            )
        );


        $fieldset = $form->addFieldset('store_labels_fieldset', array(
            'legend'       => Mage::helper('sales')->__('Store View Specific Labels'),
            'table_class'  => 'form-list stores-tree',
        ));
        $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset');
        $fieldset->setRenderer($renderer);

        foreach (Mage::app()->getWebsites() as $website) {
            $fieldset->addField("w_{$website->getId()}_label", 'note', array(
                'label'    => $website->getName(),
                'fieldset_html_class' => 'website',
            ));
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                if (count($stores) == 0) {
                    continue;
                }
                $fieldset->addField("sg_{$group->getId()}_label", 'note', array(
                    'label'    => $group->getName(),
                    'fieldset_html_class' => 'store-group',
                ));
                foreach ($stores as $store) {
                    $fieldset->addField("store_label_{$store->getId()}", 'text', array(
                        'name'      => 'store_labels['.$store->getId().']',
                        'required'  => false,
                        'label'     => $store->getName(),
                        'value'     => isset($labels[$store->getId()]) ? $labels[$store->getId()] : '',
                        'fieldset_html_class' => 'store',
                    ));
                }
            }
        }

        if ($model) {
            $form->addValues($model->getData());
        }
        $form->setAction($this->getUrl('*/quote_status/save'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
	
}