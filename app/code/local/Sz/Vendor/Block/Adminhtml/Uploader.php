<?php
class Sz_Vendor_Block_Adminhtml_Uploader extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_uploader';
        $this->_headerText = Mage::helper('vendor')->__("Process Product CSV Files");
        $this->_blockGroup = 'vendor';
        parent::__construct();
        $this->_removeButton('add');
    }

}
