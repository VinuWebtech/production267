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
 * Email log helper
 *
 * @category   Sz
 * @package    Sz_Emaillog
 */
class Sz_EmailLog_Helper_Data extends Mage_Core_Helper_Abstract
{
    /*
    public function isEnabled() {
        return Mage::getStoreConfig('system/emaillog/option') != "disabled";
    }

    public function isLogEnabled() {
        return Mage::getStoreConfig('system/emaillog/logenabled');
    }

    public function isReplyToStoreEmail() {
        return Mage::getStoreConfig('system/emaillog/store_addresses');
    }

    public function getDevelopmentMode() {
        return Mage::getStoreConfig('system/emaillog/development');
    }

    public function getGoogleApps() {
        return Mage::getStoreConfig('system/emaillog/option') == "google";
    }
    public function getSES() {
        return Mage::getStoreConfig('system/emaillog/option') == "ses";
    }

    public function getSMTP() {
        return Mage::getStoreConfig('system/emaillog/option') == "smtp";
    }
    */

    // Keeping this function for backward compatibility
    // It will be dropped eventually so call getTransport() from now on!
    public function getSMTPProTransport($id = null)
    {
        return $this->getTransport($id);
    }

    // Keeping this function for backward compatibility
    // It will be dropped eventually so call getTransport() from now on!
    public function getGoogleAppsEmailTransport($id = null)
    {
        return $this->getTransport($id);
    }

    public function getTransport($id = null)
    {
        if ($this->getSMTP()) {
            $username = Mage::getStoreConfig('system/smtpsettings/username', $id);
            $password = Mage::getStoreConfig('system/smtpsettings/password', $id);
            $host = Mage::getStoreConfig('system/smtpsettings/host', $id);
            $port = Mage::getStoreConfig('system/smtpsettings/port', $id);
            $ssl = Mage::getStoreConfig('system/smtpsettings/ssl', $id);
            $auth = Mage::getStoreConfig('system/smtpsettings/authentication', $id);

            Mage::log(
                'Preparing the SMTP Email transport, details are: \n '
                . "  username=" . $username . "\n"
                . "  password=" . "MASKED"  /*. $password  */ . "\n"
                . "  host=" . $host . "\n"
                . "  port=" . $port . "\n"
                . "  ssl=" . $ssl . "\n"
                . "  auth=" . $auth . "\n"
            );

            // Set up the config array
            $config = array();

            if ($auth != "none") {
                $config['auth'] = $auth;
                $config['username'] = $username;
                $config['password'] = $password;
            }

            if ($port) {
                $config['port'] = $port;
            }

            if ($ssl != "none" ) {
                $config['ssl'] = $ssl;
            }

            $transport = new Zend_Mail_Transport_Smtp($host, $config);
        } elseif ($this->getGoogleApps()) {
            $email = explode(",", Mage::getStoreConfig('system/googlesettings/email', $id));

            // We now allow a load balance of multiple gmail
            // accounts to get past the 500/day limit.
            if (count($email)) {
                $email = $email[array_rand($email)];
            } else {
                Mage::log(
                    "No email configured -
                    you need to specify one in the magento configuration,
                    otherwise your connection will fail"
                );
            }

            $password = Mage::getStoreConfig('system/googlesettings/gpassword', $id);

            Mage::log('Preparing the Google Apps/Gmail Email transport, email to send with is: ' . $email);
            $config = array('ssl' => 'tls', 'port' => 587, 'auth' => 'login', 'username' => $email, 'password' => $password);
            $transport = new Zend_Mail_Transport_Smtp('smtp.gmail.com', $config);
        } elseif ($this->getSES()) {
            // Big thanks to Christopher Valles
            // https://github.com/christophervalles/Amazon-SES-Zend-Mail-Transport
            include_once Mage::getBaseDir() . '/app/code/community/Aschroder/SMTPPro/lib/AmazonSES.php';

            $transport = new App_Mail_Transport_AmazonSES(
                array(
                    'accessKey' => Mage::getStoreConfig('system/sessettings/aws_access_key', $id),
                    'privateKey' => Mage::getStoreConfig('system/sessettings/aws_private_key', $id)
                )
            );
        } else {
            Mage::log("Disabled, or no matching transport");
            return null;
        }

        Mage::log("Returning transport");

        return $transport;
    }

    public function log($to, $template, $subject, $email, $isHtml)
    {
        $log = Mage::getModel('emaillog/email_log')
            ->setEmailTo($to)
            ->setTemplate($template)
            ->setSubject($subject)
            ->setEmailBody($isHtml ? $email : nl2br($email))
            ->save();
        return $this;
    }
}
