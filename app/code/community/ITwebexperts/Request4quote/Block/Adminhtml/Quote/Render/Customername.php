<?php
class ITwebexperts_Request4quote_Block_Adminhtml_Quote_Render_Customername extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row){
        return $row->getCustomerFirstname() . " " . $row->getCustomerLastname();
    }
}