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
 * @package     Sz_EmailLog
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Email log view
 *
 * @category   Sz
 * @package    Sz_EmailLog
 */

class Sz_EmailLog_Block_Log_View extends Mage_Catalog_Block_Product_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('emaillog/view.phtml');
        $this->setEmailId($this->getRequest()->getParam('email_id', false));
    }

    public function getEmailData() 
    {
        if ($this->getEmailId()) {
            return Mage::getModel('emaillog/email_log')
                ->load($this->getEmailId());
        } else {
            throw new Exception("No Email Id given");
        }
    }

    public function getBackUrl()
    {
        return Mage::helper('adminhtml')->getUrl('*/');
    }
    
    // This is an experiment in progress - if you're 
    // reading this code, maybe you'd be interested...
    
    // The idea being that during development we can include 
    // templates from the actual extension code, rather than rely on them 
    // coming from ... not entriely convinced it's a good thing...
    
    public function fetchView($fileName)
    {
        // This is so we do not need to keep templates 
        // outside of the extension code.
        $classParts = explode("_", __CLASS__);
        $devTemplatePath = Mage::getModuleDir('', $classParts[0]."_".$classParts[1]).DS."templates";
        
        if (Mage::getIsDeveloperMode() && 
            file_exists($devTemplatePath.DS.$fileName)) {
            Mage::log("NOTE: Loading template from development path - not the design directory");
            $this->setScriptPath($devTemplatePath);
        } else {
            $this->setScriptPath(Mage::getBaseDir('design'));
        }
        return parent::fetchView($fileName);
    }
}