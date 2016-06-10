<?php
class ITwebexperts_Request4quote_Block_Adminhtml_Quote_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
	
	protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));

        $quote = Mage::registry('current_quote');

        if ($quote->getId()) {
            $form->addField('entity_id', 'hidden', array(
                'name' => 'quote_id',
            ));
            $form->setValues($quote->getData());
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
	
}