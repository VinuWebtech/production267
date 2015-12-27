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
 * Observer that logs emails after they have been sent
 *
 * @category   Sz
 * @package    Sz_Emaillog
 */
class Sz_EmailLog_Model_Observer
{
    public function log($observer)
    {
        $event = $observer->getEvent();
            Mage::helper('emaillog')->log(
                $event->getEmailTo(),
                $event->getTemplate(),
                $event->getSubject(),
                $event->getEmailBody(),
                $event->getHtml()
            );
    }

    /**
     * Cron job to clear email logs from specific days
     *
     * @param mixed $schedule
     */
    public function clearEmailLogs()
    {
        $days = (int) Mage::getStoreConfig('emaillog/settings/clear_log_cron_time');
        $table  = Mage::getResourceModel('emaillog/email_log')->getMainTable();
        $where = array('log_at < ' . new Zend_Db_Expr("DATE_ADD('".now()."', INTERVAL -'{$days}' DAY)"));
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getModel('Core/Mysql4_Config')->getReadConnection();
        $connRead->delete($table, $where);
    }
}
