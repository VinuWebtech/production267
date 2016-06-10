<?php
class ITwebexperts_Request4quote_Block_Adminhtml_Quote_Create_Salesrep extends ITwebexperts_Request4quote_Block_Adminhtml_Quote_Create_Abstract
{
    public function getHeaderText(){
        return Mage::helper('request4quote')->__('Assign Sales Representative');
    }

    public function getSalesRepsOptions() {
        $chosensalesrep = $this->getQuote()->getSalesrep();
        $salesreps = '';
        $adminCollection = Mage::getModel('admin/user')->getCollection();
        foreach($adminCollection as $admin){
            $isSelected = ($admin->getUserId() == $chosensalesrep) ? 'selected' : '';
            $salesreps .= '<option value="' . $admin->getUserId() . '" ' . $isSelected . '>' . $admin->getFirstname() . ' ' . $admin->getLastname() . '</option>';
        }
        $html = '<select id="salesrep" name="salesrep">';
        $html .= '<option value="0">Select Sales Representative</option>';
        $html .= $salesreps;
        $html .= '</select>';
        return $html;
    }
}