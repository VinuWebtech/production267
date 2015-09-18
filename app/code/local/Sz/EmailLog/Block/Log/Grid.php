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
 * @author     Yugesh Ramteke <yugesh.ramteke@zeonsolutions.com>
 */
class Sz_EmailLog_Block_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('emailLogGrid');
        $this->setDefaultSort('sent');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('emaillog/email_log')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $baseUrl = $this->getUrl();

        $this->addColumn(
            'email_id',
            array(
                'header'    => Mage::helper('adminhtml')->__('Id'),
                'width'     => '30px',
                'index'     => 'email_id',
            )
        );
        $this->addColumn(
            'sent',
            array(
                'header'    => Mage::helper('adminhtml')->__('Sent'),
                'width'     => '60px',
                'index'     => 'log_at',
            )
        );
        $this->addColumn(
            'subject',
            array(
                'header'    => Mage::helper('adminhtml')->__('Subject'),
                'width'     => '160px',
                'index'     => 'subject',
            )
        );
        $this->addColumn(
            'email_to',
            array(
                'header'    => Mage::helper('adminhtml')->__('To'),
                'width'     => '160px',
                'index'     => 'email_to',
            )
        );

        return parent::_prepareColumns();
    }

    /**
     * Row click url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/view', array('email_id' => $row->getId()));
    }
}
