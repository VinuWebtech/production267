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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('request4quote/quote'), 'r4q_status',array(
    'type'  =>  Varien_Db_Ddl_Table::TYPE_TEXT,
    'comment'   =>  'Status'));

$installer->getConnection()->addColumn($installer->getTable('request4quote/quote'), 'r4q_note',array(
    'type'  =>  Varien_Db_Ddl_Table::TYPE_TEXT,
    'comment'   =>  'Note'));

$installer->getConnection()->addColumn($installer->getTable('request4quote/quote'), 'r4q_phone',array(
    'type'  =>  Varien_Db_Ddl_Table::TYPE_TEXT,
    'comment'   =>  'Phone'));

$installer->getConnection()->addColumn($installer->getTable('request4quote/quote_item'), 'r4q_note',array(
    'type'  =>  Varien_Db_Ddl_Table::TYPE_TEXT,
    'comment'   =>  'Status'));

$installer->endSetup();