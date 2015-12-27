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
 * @package   Sz_Emaillog
 * @copyright Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license   http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Email log model
 * 
 * @category Sz
 * @package  Sz_Emaillog
 */
class Sz_EmailLog_Model_Email extends Mage_Core_Model_Email
{
    public function send()
    {
        $body = $this->getBody();
        Mage::dispatchEvent(
            'emaillog_email_before_send',
            array(
                'email_to' => $this->getToName(),
                'subject' => $this->getSubject(),
                'template' => "n/a", //TODO: is that true?
                'html' => (strtolower($this->getType()) == 'html'),
                'email_body' => $body
            )
        );
        
        if (Mage::getStoreConfigFlag('system/smtp/disable')) {
            return $this;
        }

        $mail = new Zend_Mail();

        if (strtolower($this->getType()) == 'html') {
            $mail->setBodyHtml($this->getBody());
        } else {
            $mail->setBodyText($this->getBody());
        }

        $mail->setFrom($this->getFromEmail(), $this->getFromName())
            ->addTo($this->getToEmail(), $this->getToName())
            ->setSubject($this->getSubject());
        $mail->send();

        return $this;
    }
}
