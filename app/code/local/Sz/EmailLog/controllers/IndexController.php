<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
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
 * @category    Sz
 * @package     Sz_Emaillog
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Email log index controller
 *
 * @category    Sz
 * @package     Sz_Emaillog
 */
class Sz_EmailLog_IndexController	extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->loadLayout()
            ->_setActiveMenu('sz')
            ->_addBreadcrumb(
                Mage::helper('adminhtml')->__('FQS'),
                Mage::helper('adminhtml')->__('FQS')
            )
            ->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Email Log'),
                Mage::helper('adminhtml')->__('Email Log')
            );
        return $this;
    }

    public function indexAction()
    {

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('emaillog/log'))
            ->renderLayout();
    }

    public function viewAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('emaillog/log_view'))
            ->renderLayout();
    }
}
