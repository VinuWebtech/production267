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
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @copyright  Copyright (c) 2006-2014 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Onepage controller for checkout
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
require_once("Mage/Checkout/controllers/OnepageController.php");
class Sz_Checkout_OnepageController extends Mage_Checkout_OnepageController
{
    const ZIPCODE_FILE_DIRECTORY = 'address';
    const ZIPCODE_FILE_NAME = 'zipcode.csv';

    /**
     * List of functions for section update
     *
     * @var array
     */
    protected $_sectionUpdateFunctions = array(
        'payment-method'  => '_getPaymentMethodsHtml',
        'review'          => '_getReviewHtml',
    );


    /**
     * Save checkout method
     */
    public function saveMethodAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $method = $this->getRequest()->getPost('method');
            $email = $this->getRequest()->getParam('customer_email');
            $error = $this->_validateEmail($email);
            $result = $this->getOnepage()->saveCheckoutMethod($method);
            if ($error !== true) {
                $result['message'] = array_values($error);
            }

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    protected function _validateEmail($value = null)
    {
        if ($value) {
            $validator = new Zend_Validate_EmailAddress();
            $validator->setMessage(
                Mage::helper('eav')->__('"%s" invalid type entered.', $value),
                Zend_Validate_EmailAddress::INVALID
            );
            $validator->setMessage(
                Mage::helper('eav')->__('"%s" is not a valid email address.', $value),
                Zend_Validate_EmailAddress::INVALID_FORMAT
            );
            $validator->setMessage(
                Mage::helper('eav')->__('"%s" is not a valid hostname.', $value),
                Zend_Validate_EmailAddress::INVALID_HOSTNAME
            );
            $validator->setMessage(
                Mage::helper('eav')->__('"%s" is not a valid hostname.', $value),
                Zend_Validate_EmailAddress::INVALID_MX_RECORD
            );
            $validator->setMessage(
                Mage::helper('eav')->__('"%s" is not a valid hostname.', $value),
                Zend_Validate_EmailAddress::INVALID_MX_RECORD
            );
            $validator->setMessage(
                Mage::helper('eav')->__('"%s" is not a valid email address.', $value),
                Zend_Validate_EmailAddress::DOT_ATOM
            );
            $validator->setMessage(
                Mage::helper('eav')->__('"%s" is not a valid email address.', $value),
                Zend_Validate_EmailAddress::QUOTED_STRING
            );
            $validator->setMessage(
                Mage::helper('eav')->__('"%s" is not a valid email address.', $value),
                Zend_Validate_EmailAddress::INVALID_LOCAL_PART
            );
            $validator->setMessage(
                Mage::helper('eav')->__('"%s" exceeds the allowed length.', $value),
                Zend_Validate_EmailAddress::LENGTH_EXCEEDED
            );
            $validator->setMessage(
                Mage::helper('customer')->__("'%value%' appears to be an IP address, but IP addresses are not allowed"),
                Zend_Validate_Hostname::IP_ADDRESS_NOT_ALLOWED
            );
            $validator->setMessage(
                Mage::helper('customer')->__("'%value%' appears to be a DNS hostname but cannot match TLD against known list"),
                Zend_Validate_Hostname::UNKNOWN_TLD
            );
            $validator->setMessage(
                Mage::helper('customer')->__("'%value%' appears to be a DNS hostname but contains a dash in an invalid position"),
                Zend_Validate_Hostname::INVALID_DASH
            );
            $validator->setMessage(
                Mage::helper('customer')->__("'%value%' appears to be a DNS hostname but cannot match against hostname schema for TLD '%tld%'"),
                Zend_Validate_Hostname::INVALID_HOSTNAME_SCHEMA
            );
            $validator->setMessage(
                Mage::helper('customer')->__("'%value%' appears to be a DNS hostname but cannot extract TLD part"),
                Zend_Validate_Hostname::UNDECIPHERABLE_TLD
            );
            $validator->setMessage(
                Mage::helper('customer')->__("'%value%' does not appear to be a valid local network name"),
                Zend_Validate_Hostname::INVALID_LOCAL_NAME
            );
            $validator->setMessage(
                Mage::helper('customer')->__("'%value%' appears to be a local network name but local network names are not allowed"),
                Zend_Validate_Hostname::LOCAL_NAME_NOT_ALLOWED
            );
            $validator->setMessage(
                Mage::helper('customer')->__("'%value%' appears to be a DNS hostname but the given punycode notation cannot be decoded"),
                Zend_Validate_Hostname::CANNOT_DECODE_PUNYCODE
            );
            if (!$validator->isValid($value)) {
                return array_unique($validator->getMessages());
            }
        }
        return;
    }
    /*
     * Check the email if its already registered
     */
    public function checkEmailAction()
    {
        $bool = 0;
        $email = $this->getRequest()->getParam('email');
        $websiteId = Mage::app()->getWebsite()->getId();
        $customer = Mage::getModel('customer/customer');
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($email);
        if ($customer->getId()) {
            $bool = 1;
        }
        $info =  array( "status" => $bool);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($info));
    }

    /**
     * Login post action
     */
    public function loginPostAction()
    {
        $response = array(
            'status' => false,
            'msg' => '',
        );
        $session = Mage::getSingleton('customer/session');
        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost();
            if (!empty($login['email']) && !empty($login['password'])) {
                try {
                    $session->login($login['email'], $login['password']);
                    if ($session->getCustomer()->getId()) {
                        $response['status'] = true;
                    }
                } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $value = $this->_getHelper('customer')->getEmailConfirmationUrl($login['username']);
                            $message = $this->_getHelper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            break;
                        default:
                            $message = $e->getMessage();
                    }
                    $response['msg'] = $message;
                    $response['status'] = false;
                    $session->setUsername($login['username']);
                } catch (Exception $e) {
                    $response['status'] = false;
                    // Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
                }
            } else {
                $response['msg'] = $this->__('Login and password are required.');
                $response['status'] = false;
            }
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }

    /*
     * Function to fetch the
     * city,state and country based on zipcode value
     */
    /*
 * Check the email if its already registered
 */
    public function fetchAddressDetailAction()
    {
        $response = array(
            'state' => '',
            'city' => '',
            'status' => false,
            'err-msg' => ''
        );
        if ($this->getRequest()->isPost()) {
            $zipCode =$this->getRequest()->getPost('zipcode');  //get value from ajax
            if ($zipCode) {
                try {
                    $filename= Mage::getBaseDir('var').DS.self::ZIPCODE_FILE_DIRECTORY.DS.
                        self::ZIPCODE_FILE_NAME; //zipcode csv file(must reside in same folder)
                    $f = fopen($filename, 'r');
                    while ($row = fgetcsv($f))
                    {
                        if ($row[0] == $zipCode) //1 mean number of column of zipcode
                        {
                            $response['city']   =   $row[1];  //2- Number of city column
                            $response['state']  =   $row[2]; //3-Number of state column
                            $response['status'] = true;
                            break;
                        }
                    }
                    fclose($f);
                } catch (exception $e) {
                    $response['err-msg'] = $e->getMessage();
                }
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }

    /**
     * Save checkout billing address
     */
    public function saveBillingAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('billing', array());
            $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);

            if (isset($data['email'])) {
                $data['email'] = trim($data['email']);
            }
            $result = $this->getOnepage()->saveBilling($data, $customerAddressId);

            if (!isset($result['error'])) {
                $result['goto_section'] = 'payment';
                $result['update_section'] = array(
                    'name' => 'payment-method',
                    'html' => $this->_getPaymentMethodsHtml()
                );
            }
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }
}
