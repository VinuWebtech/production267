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
 * @category  Sz
 * @package   Sz_EmailLog
 * @copyright Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license   http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Email log block
 * 
 * @category Sz
 * @package  Sz_EmailLog
 */
class Sz_EmailLog_Block_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Block constructor
     */
    public function __construct() 
    {
        $this->_blockGroup = 'emaillog';
        $this->_controller = 'log';
        $this->_headerText = Mage::helper('cms')->__('Email Log');
        parent::__construct();
        
        // Remove the add button
        $this->_removeButton('add');
    }
}
