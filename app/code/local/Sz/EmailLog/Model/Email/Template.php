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
 * Email log email template model
 *
 * @category Sz
 * @package  Sz_Emaillog
 */
class Sz_EmailLog_Model_Email_Template extends Mage_Core_Model_Email_Template
{
    /**
     * Send mail to recipient
     *
     * @param   array|string       $email        E-mail(s)
     * @param   array|string|null  $name         receiver name(s)
     * @param   array              $variables    template variables
     * @return  boolean
     **/
    public function send($email, $name = null, array $variables = array())
    {
        $emails = array_values((array)$email);
        // Record one email for each receipient
        foreach ($emails as $key => $email) {
            Mage::dispatchEvent(
                'emaillog_email_before_send',
                array(
                    'email_to' => $email,
                    'template' => $this->getTemplateId(),
                    'subject' => $this->getProcessedTemplateSubject($variables),
                    'html' => !$this->isPlain(),
                    'email_body' => $this->getProcessedTemplate($variables, true)
                )
            );
        }

        if (!$this->isValidForSend()) {
            // translation is intentionally omitted
            Mage::logException(new Exception('This letter cannot be sent.'));
            return false;
        }

        //$emails = array_values((array)$email);
        $names = is_array($name) ? $name : (array)$name;
        $names = array_values($names);
        foreach ($emails as $key => $email) {
            if (!isset($names[$key])) {
                $names[$key] = substr($email, 0, strpos($email, '@'));
            }
        }

        $variables['email'] = reset($emails);
        $variables['name'] = reset($names);

        ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
        ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));

        $mail = $this->getMail();

        $setReturnPath = Mage::getStoreConfig(self::XML_PATH_SENDING_SET_RETURN_PATH);
        switch ($setReturnPath) {
            case 1:
                $returnPathEmail = $this->getSenderEmail();
                break;
            case 2:
                $returnPathEmail = Mage::getStoreConfig(self::XML_PATH_SENDING_RETURN_PATH_EMAIL);
                break;
            default:
                $returnPathEmail = null;
                break;
        }

        if ($returnPathEmail !== null) {
            $mailTransport = new Zend_Mail_Transport_Sendmail("-f".$returnPathEmail);
            Zend_Mail::setDefaultTransport($mailTransport);
        }

        foreach ($emails as $key => $email) {
            $mail->addTo(
                $email,
                '=?utf-8?B?' . base64_encode($names[$key]) . '?='
            );
        }

        $this->setUseAbsoluteLinks(true);
        $text = $this->getProcessedTemplate($variables, true);

        if ($this->isPlain()) {
            $mail->setBodyText($text);
        } else {
            $mail->setBodyHTML($text);
        }

        $mail->setSubject(
            '=?utf-8?B?'
            . base64_encode($this->getProcessedTemplateSubject($variables))
            .'?='
        );
        $mail->setFrom($this->getSenderEmail(), $this->getSenderName());

        try {
            $mail->send();
            $this->_mail = null;
        }
        catch (Exception $e) {
            $this->_mail = null;
            Mage::logException($e);
            return false;
        }

        return true;
    }
}
