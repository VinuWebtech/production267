<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Adminhtml sales order create billing address block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class ITwebexperts_Request4quote_Block_Adminhtml_Quote_Create_Billing_Address
    extends ITwebexperts_Request4quote_Block_Adminhtml_Quote_Create_Form_Address
{
    /**
     * Return header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return Mage::helper('request4quote')->__('Billing Address');
    }

    /**
     * Return Header CSS Class
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'head-billing-address';
    }

    /**
     * Prepare Form and add elements to form
     *
     * @return Mage_Adminhtml_Block_Sales_Order_Create_Billing_Address
     */
    protected function _prepareForm()
    {
        $this->setJsVariablePrefix('billingAddress');
        parent::_prepareForm();

        $this->_form->addFieldNameSuffix('order[billing_address]');
        $this->_form->setHtmlNamePrefix('order[billing_address]');
        $this->_form->setHtmlIdPrefix('order-billing_address_');

        return $this;
    }

    /**
     * Return Form Elements values
     *
     * @return array
     */
    public function getFormValues()
    {
        return $this->getCreateOrderModel()->getBillingAddress()->getData();
    }

    /**
     * Return customer address id
     *
     * @return int|boolean
     */
    public function getAddressId()
    {
        return $this->getCreateOrderModel()->getBillingAddress()->getCustomerAddressId();
    }

    /**
     * Return billing address object
     *
     * @return Mage_Customer_Model_Address
     */
    public function getAddress()
    {
        return $this->getCreateOrderModel()->getBillingAddress();
    }

    /**
     * Returns module helper.
     * @return ITwebexperts_Request4quote_Helper_Data
     */
    public function getModuleHelper()
    {
        return Mage::helper('request4quote');
    }

    /**
     * Returns true if we can show the block.
     *
     * @return bool
     */
    public function canShow()
    {
        return $this->getModuleHelper()->canShowBillingAddressAdmin();
    }

    /**
     * Returns true if address details are mandatory.
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->getModuleHelper()->isBillingAddressRequiredAdmin();
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $html = parent::_toHtml();
        /*
        if (!$this->isRequired()) {
            $html = $this->getModuleHelper()->removeRequired($html);

            // Change zipcode id so events cannot fire on that element.
            //$html = str_replace('order-billing_address_postcode', 'billing_address_postcode', $html);
        }
        */
        return $html;
    }
}
