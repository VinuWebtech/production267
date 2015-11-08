<?php
require_once Mage::getModuleDir('controllers', 'Mage_Customer').DS.'AccountController.php';
class Sz_Ajaxlogin_AccountController extends Mage_Customer_AccountController
{
      protected $_validActions = array('create','login','logoutSuccess','forgotpassword','forgotpasswordpost','confirm','confirmation','resetpassword','resetpasswordpost');
      protected $_customActions = array('szLogin','szCreate','szForgotPass');

     public function preDispatch()
     {
              $action = $this->getRequest()->getActionName();

           if (preg_match('/^('.$this->_getCustomActions().')/i', $action))
           {
            $this->getRequest()->setActionName($this->_validActions[1]);
           }

           parent::preDispatch();

           /**
            * Parent check is complete, reset request action name to origional value
            */
           if ($action != $this->getRequest()->getActionName())
           {
            $this->getRequest()->setActionName($action);
           }

           if (!$this->getRequest()->isDispatched()) {
            return;
           }

           if (!preg_match('/^('.$this->_getValidActions().')/i', $action)) {
            if (!$this->_getSession()->authenticate($this)) {
             $this->setFlag('', 'no-dispatch', true);
            }
           } else {
            $this->_getSession()->setNoReferer(true);
           }

     }
      protected function _getValidActions()
      {
      return implode("|", array_merge($this->_validActions, $this->_customActions));
      }

     /**
      * Gets custom action names and returns them as a pipe separated string
      *
      * @return string
      */
     protected function _getCustomActions()
     {
      return implode("|", $this->_customActions);
     }

    public function szLoginAction()
    {
        if ($this->_getSession()->isLoggedIn()) {
             return;
        }
        $session = $this->_getSession();

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
               /* $error = $this->_validateEmail($login['username']);
                if ($error !== true) {
                    $response['message'] =implode(', ',array_values($error))?implode(', ',array_values($error)):
                        Mage::helper('customer')->__('Invalid email address.');
                    $response['success'] = false;
                    $this->getResponse()->setBody(Mage::helper( 'core')->jsonEncode( $response ));
                    return;
                }*/
                try {
                    $session->login($login['username'], $login['password']);
                    if ($session->getCustomer()->getIsJustConfirmed()) {
                        $this->_welcomeCustomer($session->getCustomer(), true);
                    }
                    $response['success'] = true;
                    $response['message'] = Mage::helper('customer')->__('You have logged in successfully.');
                    $response['url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

                } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $value = Mage::helper('customer')->getEmailConfirmationUrl($login['username']);
                            $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            break;
                        default:
                            $message = $e->getMessage();
                    }
                    //$session->addError($message);
                    $response['message'] = $message;
                    $response['success'] = false;
                    $session->setUsername($login['username']);
                } catch (Exception $e) {
                    // Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
                }
            } else {
                //$session->addError($this->__('Login and password are required.'));
                $message = 'Login and password are required.';
                $response['success'] = false;
                $response['message'] = $message;
            }
        }
        $this->getResponse()->setBody(Mage::helper( 'core')->jsonEncode( $response ));
        //$this->_loginPostRedirect();
    }


    /**
     * Login post action
     */
    public function szCreateAction()
    {
        $session = $this->_getSession();
        if ($session->isLoggedIn()) {
            return;
        }
        $session->setEscapeMessages(true); // prevent XSS injection in user input
        if ($this->getRequest()->isPost()) {
            $errors = array();
            /*$error = $this->_validateEmail($this->getRequest()->getParam('email'));
            if ($error !== true) {
                $response['message'] =implode(', ',array_values($error))?implode(', ',array_values($error)):
                    Mage::helper('customer')->__('Invalid email address.');
                $response['success'] = false;
                $this->getResponse()->setBody(Mage::helper( 'core')->jsonEncode( $response ));
                return;
            }*/
            if (!$customer = Mage::registry('current_customer')) {
                $customer = Mage::getModel('customer/customer')->setId(null);
            }

            /* @var $customerForm Mage_Customer_Model_Form */
            $customerForm = Mage::getModel('customer/form');
            $customerForm->setFormCode('customer_account_create')
                ->setEntity($customer);

            $customerData = $customerForm->extractData($this->getRequest());

            if ($this->getRequest()->getParam('is_subscribed', false)) {
                $customer->setIsSubscribed(1);
            }

            /**
             * Initialize customer group id
             */
            $customer->getGroupId();

            if ($this->getRequest()->getPost('create_address')) {
                /* @var $address Mage_Customer_Model_Address */
                $address = Mage::getModel('customer/address');
                /* @var $addressForm Mage_Customer_Model_Form */
                $addressForm = Mage::getModel('customer/form');
                $addressForm->setFormCode('customer_register_address')
                    ->setEntity($address);

                $addressData    = $addressForm->extractData($this->getRequest(), 'address', false);
                $addressErrors  = $addressForm->validateData($addressData);
                if ($addressErrors === true) {
                    $address->setId(null)
                        ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
                        ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));
                    $addressForm->compactData($addressData);
                    $customer->addAddress($address);

                    $addressErrors = $address->validate();
                    if (is_array($addressErrors)) {
                        $errors = array_merge($errors, $addressErrors);
                    }
                } else {
                    $errors = array_merge($errors, $addressErrors);
                }
            }

            try {
                $customerErrors = $customerForm->validateData($customerData);
                if ($customerErrors !== true) {
                    $errors = array_merge($customerErrors, $errors);
                } else {
                    $customerForm->compactData($customerData);
                    $customer->setPassword($this->getRequest()->getPost('password'));
                    $customer->setConfirmation($this->getRequest()->getPost('confirmation'));
                    $customer->setPasswordConfirmation($this->getRequest()->getPost('confirmation'));
                    $customerErrors = $customer->validate();
                    if (is_array($customerErrors)) {
                        $errors = array_merge($customerErrors, $errors);
                    }
                }

                $validationResult = count($errors) == 0;
                $response = array();
                if (true === $validationResult) {
                    $customer->save();

                    if ($customer->isConfirmationRequired()) {
                        $customer->sendNewAccountEmail('confirmation', $session->getBeforeAuthUrl());
                          $response['success'] = true;
                          $response['message'] = $this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.', Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail()));
                          $response['url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

                    } else {
                        $session->setCustomerAsLoggedIn($customer);
                        $url = $this->_welcomeCustomer($customer);
                        $response['success'] = true;
                        $response['url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
                        $response['message'] = $this->__('You are successfully registered');
                    }
                } else {
                    $session->setCustomerFormData($this->getRequest()->getPost());
                    if (is_array($errors)) {
                        $response['success'] = false;
                        foreach ($errors as $errorMessage) {
                            $response['message'] .= $errorMessage;
                        }
                    } else {
                        $response['success'] = false;
                        $response['message'] = $this->__('Invalid customer data');
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $session->setCustomerFormData($this->getRequest()->getPost());
                if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                    $url = Mage::getUrl('customer/account/forgotpassword');
                    $response['success'] = false;
                    $response['message'] = $this->__('There is already an account with this email address. If you are sure that it is your email address.');

                } else {
                    $response['success'] = false;
                    $response['message'] = $e->getMessage();

                }

            } catch (Exception $e) {
                  $response['success'] = false;
                $response['message'] = $this->__('Cannot save the customer.');
            }
        }
        $this->getResponse()->setBody(Mage::helper( 'core')->jsonEncode( $response ));
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

    public function szForgotPassAction()
    {
        $email = (string) $this->getRequest()->getPost('email');

        if ($email) {
            if (!Zend_Validate::is($email, 'EmailAddress')) {
                $this->_getSession()->setForgottenEmail($email);
                //$this->_getSession()->addError($this->__('Invalid email address.'));
                // $this->_redirect('*/*/forgotpassword');
                $response['url'] = Mage::getUrl('*/*/forgotpassword');
                $response['message'] = Mage::helper('customer')->__('Invalid email address.');
                $response['success'] = false;
                $this->getResponse()->setBody(Mage::helper( 'core')->jsonEncode( $response ));
                return;
            }

            /** @var $customer Mage_Customer_Model_Customer */
            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($email);

            if ($customer->getId()) {
                try {
                    $newResetPasswordLinkToken = Mage::helper('customer')->generateResetPasswordLinkToken();
                    $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                    $customer->sendPasswordResetConfirmationEmail();
                    $response['message'] = Mage::helper('customer')->__('You will receive an email at %s with a link to reset your password.', Mage::helper('customer')->htmlEscape($email));
                    $response['success'] = true;
                    $this->getResponse()->setBody(Mage::helper( 'core')->jsonEncode( $response ));
                    return;
                } catch (Exception $exception) {
                    $this->_getSession()->addError($exception->getMessage());
                    //$this->_redirect('*/*/forgotpassword');
                    $response['url'] = Mage::getUrl('*/*/forgotpassword');
                    $response['message'] = $exception->getMessage();
                    $response['success'] = false;
                    $this->getResponse()->setBody(Mage::helper( 'core')->jsonEncode( $response ));
                    return;
                }
            } else {
                $response['message'] = Mage::helper('customer')->__(
                    'There is no account associated with %s',
                    Mage::helper('customer')->htmlEscape($email)
                );
                $response['success'] = false;
                $this->getResponse()->setBody(Mage::helper( 'core')->jsonEncode( $response ));
                return;
            }
        } else {
            $response['url'] = Mage::getUrl('*/*/forgotpassword');
            $response['message'] = $this->__('Please enter your email.');
            $response['success'] = false; 
            $this->getResponse()->setBody(Mage::helper( 'core')->jsonEncode( $response ));
            return;
        }
        
        $this->getResponse()->setBody(Mage::helper( 'core')->jsonEncode( $response ));
    }

}