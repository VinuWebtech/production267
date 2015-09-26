<?php
class Sz_Vendor_Block_Adminhtml_Uploader extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_uploader';
        $headerSuffix= '';
        $vendorId = $this->getRequest()->getParam('id', 0);
        if ($vendorId) {
            $customer = Mage::getModel('customer/customer')->load($vendorId);
            $headerSuffix .= $customer->getName().'('.$customer->getEmail().')';
        }
        $this->_headerText = Mage::helper('vendor')->__("Process Product CSV Files").' For '.$headerSuffix;
        $this->_blockGroup = 'vendor';
        parent::__construct();
        $this->_removeButton('add');
        $this->_addButton('back', array(
            'label'     => 'Back To Vendors Page',
            'onclick'   => 'setLocation(\'' . $this->getVendorGridUrl() .'\')',
            'class'     => '',
        ));
    }

    public function getVendorGridUrl(){
        return $this->getUrl('vendor/adminhtml_partners/');
    }
}
