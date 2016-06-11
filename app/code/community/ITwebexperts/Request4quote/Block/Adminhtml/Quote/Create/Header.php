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
 * Create order form header
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class ITwebexperts_Request4quote_Block_Adminhtml_Quote_Create_Header extends ITwebexperts_Request4quote_Block_Adminhtml_Quote_Create_Abstract
{
    protected function _toHtml()
    {
        if ($this->_getSession()->getQuote()->getId() && $this->getRequest()->getControllerName() == 'adminhtml_quote_edit') {
            return '<h3 class="icon-head head-sales-order">'.Mage::helper('request4quote')->__('Edit Quote #%s for %s', $this->_getSession()->getQuote()->getId(),$this->getCustomer()->getName()).'</h3>';
        }

        $customerId = $this->getCustomerId();
        $storeId    = $this->getStoreId();
        $out = '';
        if ($customerId && $storeId) {
            $out.= Mage::helper('request4quote')->__('Create New Quote Request for %s in %s', $this->getCustomer()->getName(), $this->getStore()->getName());
        }
        elseif (!is_null($customerId) && $storeId){
            $out.= Mage::helper('request4quote')->__('Create New Quote Request for New Customer in %s', $this->getStore()->getName());
        }
        elseif ($customerId) {
            $out.= Mage::helper('request4quote')->__('Create New Quote Request for %s', $this->getCustomer()->getName());
        }
        elseif (!is_null($customerId)){
            $out.= Mage::helper('request4quote')->__('Create New Quote Request for New Customer');
        }
        else {
            $out.= Mage::helper('request4quote')->__('Create New Quote Request');
        }
        $out = $this->htmlEscape($out);
        $out = '<h3 class="icon-head head-sales-order">' . $out . '</h3>';
        return $out;
    }
}
