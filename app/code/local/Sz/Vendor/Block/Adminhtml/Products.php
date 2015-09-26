<?php
class Sz_Vendor_Block_Adminhtml_Products extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_products';
        $vendorId = $this->getRequest()->getParam('vendor_id', 0);
        $headerSuffix= '';
        if ($vendorId) {
            $customer = Mage::getModel('customer/customer')->load($vendorId);
            $headerSuffix .= $customer->getName().'('.$customer->getEmail().')';
        }
        $this->_headerText = Mage::helper('vendor')->__("Manage Vendor's Product").' For '.$headerSuffix;
        $this->_blockGroup = 'vendor';
        parent::__construct();
        $this->_removeButton('add');

        if (!$vendorId) {
            $this->_addButton('suo', array(
                'label'     => 'Show Unapproved Only',
                'onclick'   => 'setLocation(\'' . $this->getShowUnapprovedOnlyUrl() .'\')',
                'class'     => '',
            ));
        } else {
            $this->_addButton('back', array(
                'label'     => 'Back To Vendors Page',
                'onclick'   => 'setLocation(\'' . $this->getVendorGridUrl() .'\')',
                'class'     => '',
            ));
        }
    }

    public function getShowUnapprovedOnlyUrl(){
        return $this->getUrl('*/*/index/unapp/1');
    }

    public function getVendorGridUrl(){
        return $this->getUrl('vendor/adminhtml_partners/');
    }
}
