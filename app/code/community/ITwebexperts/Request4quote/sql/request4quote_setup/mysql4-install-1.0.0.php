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

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('request4quote/quote')};

CREATE TABLE {$this->getTable('request4quote/quote')} LIKE {$this->getTable('sales/quote')};

-- DROP TABLE IF EXISTS {$this->getTable('request4quote/quote_item')};

CREATE TABLE {$this->getTable('request4quote/quote_item')} LIKE {$this->getTable('sales/quote_item')};

-- DROP TABLE IF EXISTS {$this->getTable('request4quote/quote_item_option')};

CREATE TABLE {$this->getTable('request4quote/quote_item_option')} LIKE {$this->getTable('sales/quote_item_option')};

-- DROP TABLE IF EXISTS {$this->getTable('request4quote/quote_address')};

CREATE TABLE {$this->getTable('request4quote/quote_address')} LIKE {$this->getTable('sales/quote_address')};

-- DROP TABLE IF EXISTS {$this->getTable('request4quote/quote_address_item')};

CREATE TABLE {$this->getTable('request4quote/quote_address_item')} LIKE {$this->getTable('sales/quote_address_item')};

-- DROP TABLE IF EXISTS {$this->getTable('request4quote/quote_address_shipping_rate')};

CREATE TABLE {$this->getTable('request4quote/quote_address_shipping_rate')} LIKE {$this->getTable('sales/quote_address_shipping_rate')};

-- DROP TABLE IF EXISTS {$this->getTable('request4quote/quote_payment')};

CREATE TABLE {$this->getTable('request4quote/quote_payment')} LIKE {$this->getTable('sales/quote_payment')};

");

$installer->endSetup();
