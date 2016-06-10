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
 * Adminhtml sales order create
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class ITwebexperts_Request4quote_Block_Adminhtml_Quote_Create extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId    = 'quote_id';
        $this->_blockGroup = 'request4quote';
        $this->_controller = 'adminhtml_quote';
        $this->_mode = 'create';

        parent::__construct();

        $this->setId('sales_order_create');

        $customerId = $this->_getSession()->getCustomerId();
        $storeId    = $this->_getSession()->getStoreId();
        $quote = $this->_getSession()->getQuote();

        $this->_updateButton('save', 'label', Mage::helper('request4quote')->__('Submit Quote'));
        $this->_updateButton('save', 'onclick', "order.submit()");
        $this->_updateButton('save', 'id', 'submit_order_top_button');
        if (is_null($customerId) || !$storeId) {
            $this->_updateButton('save', 'style', 'display:none');
        }

        $this->_updateButton('back', 'id', 'back_order_top_button');
        $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getBackUrl() . '\')');

        $this->_updateButton('reset', 'id', 'reset_order_top_button');

        if (is_null($customerId)) {
            $this->_updateButton('reset', 'style', 'display:none');
        }
//        else {
//            $this->_updateButton('back', 'style', 'display:none');
//        }

        $confirm = Mage::helper('request4quote')->__('Are you sure you want to cancel this quote?');
        $this->_updateButton('reset', 'label', Mage::helper('request4quote')->__('Cancel'));
        $this->_updateButton('reset', 'class', 'cancel');
        $this->_updateButton('reset', 'onclick', 'deleteConfirm(\''.$confirm.'\', \'' . $this->getCancelUrl() . '\')');

        if($quote->getR4qStatus()) {
            $this->_addButton('print', array(
                'label' => Mage::helper('request4quote')->__('Print'),
                'onclick' => 'setLocation(\'' . $this->getPrintUrl() . '\')'
            ));
        }

        if($quote->getR4qStatus()) {
            $this->_addButton('quote_order', array(
                'label' => Mage::helper('request4quote')->__('Convert to Order'),
                'onclick' => "window.location.href = '" . $this->getUrl('adminhtml/quote/order', array('quote_id' => $quote->getId())) . "'; return false;"
            ));
        }
    }

    public function getPrintUrl()
    {
        $quote = $this->_getSession()->getQuote();
        return $this->getUrl('adminhtml/quote/printQuote',array(
            'quote_id' => $quote->getId()
        ));
    }

    /**
     * Prepare header html
     *
     * @return string
     */
    public function getHeaderHtml()
    {
        $out = '<div id="order-header">'
            . $this->getLayout()->createBlock('request4quote/adminhtml_quote_create_header')->toHtml()
            . '</div>';
        return $out;
    }

    /**
     * Prepare form html. Add block for configurable product modification interface
     *
     * @return string
     */
    public function getFormHtml()
    {
        $html = parent::getFormHtml();
        $html .= $this->getLayout()->createBlock('adminhtml/catalog_product_composite_configure')->toHtml();
        return $html;
    }

    public function getHeaderWidth()
    {
        return 'width: 70%;';
    }

    /**
     * Retrieve quote session object
     *
     * @return Mage_Adminhtml_Model_Session_Quote
     */
    protected function _getSession()
    {
        return Mage::getSingleton('request4quote/adminhtml_session_quote');
    }

    public function getCancelUrl()
    {
        if ($this->_getSession()->getOrder()->getId()) {
            $url = $this->getUrl('*/sales_order/view', array(
                'order_id' => Mage::getSingleton('request4quote/adminhtml_session_quote')->getOrder()->getId()
            ));
        } else {
            $url = $this->getUrl('*/*/cancel');
        }

        return $url;
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/quote/');
    }
}
