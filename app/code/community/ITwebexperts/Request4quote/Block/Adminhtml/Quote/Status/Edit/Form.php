<?php
class ITwebexperts_Request4quote_Block_Adminhtml_Quote_Status_Edit_Form extends ITwebexperts_Request4quote_Block_Adminhtml_Quote_Status_New_Form {
	
	public function __construct()
    {
        parent::__construct();
        $this->setId('new_quote_status');
    }
	
	protected function _prepareForm()
    {
        parent::_prepareForm();
        $form = $this->getForm();
        $form->getElement('base_fieldset')->removeField('is_new');
		$form->getElement('base_fieldset')
			->getElements()
			->searchById('status')
			->setReadonly(true, true);
        $form->setAction(
            $this->getUrl('*/quote_status/save', array('status'=>$this->getRequest()->getParam('status')))
        );
        return $this;
    }
	
}