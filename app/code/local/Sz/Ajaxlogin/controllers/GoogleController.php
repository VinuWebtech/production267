<?php

class Sz_Ajaxlogin_GoogleController extends Mage_Core_Controller_Front_Action
{
    protected $referer = null;

    public function connectAction()
    {
        try {
            $this->_connectCallback();
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }

        if(!empty($this->referer)) {
            $this->_redirectUrl($this->referer);
        } else {
            Mage::helper('ajaxlogin')->redirect404($this);
        }
    }

    public function disconnectAction()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();

        try {
            $this->_disconnectCallback($customer);
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }

        if(!empty($this->referer)) {
            $this->_redirectUrl($this->referer);
        } else {
            Mage::helper('ajaxlogin')->redirect404($this);
        }
    }

    protected function _disconnectCallback(Mage_Customer_Model_Customer $customer) {
        $this->referer = Mage::getUrl('socialconnect/account/google');        
        
        Mage::helper('ajaxlogin/google')->disconnect($customer);
        
        Mage::getSingleton('core/session')
            ->addSuccess(
                $this->__('You have successfully disconnected your Google account from our store account.')
            );
    }

    protected function _connectCallback() {
        $errorCode = $this->getRequest()->getParam('error');
        $code = $this->getRequest()->getParam('code');
        $state = $this->getRequest()->getParam('state');
        if(!($errorCode || $code) && !$state) {
            // Direct route access - deny
            return;
        }
        
        $this->referer = Mage::getSingleton('core/session')->getGoogleRedirect();

        if(!$state || $state != Mage::getSingleton('core/session')->getGoogleCsrf()) {
            return;
        }

        if($errorCode) {
            // Google API read light - abort
            if($errorCode === 'access_denied') {
                Mage::getSingleton('core/session')
                    ->addNotice(
                        $this->__('Google Connect process aborted.')
                    );

                return;
            }

            throw new Exception(
                sprintf(
                    $this->__('Sorry, "%s" error occured. Please try again.'),
                    $errorCode
                )
            );

            return;
        }

        if ($code) {
            // Google API green light - proceed
            $client = Mage::getSingleton('ajaxlogin/google_client');

            $userInfo = $client->api('/userinfo');
            $token = $client->getAccessToken();

            $customersByGoogleId = Mage::helper('ajaxlogin/google')
                ->getCustomersByGoogleId($userInfo->id);

            if(Mage::getSingleton('customer/session')->isLoggedIn()) {
                // Logged in user
                if($customersByGoogleId->count()) {
                    // Google account already connected to other account - deny
                    Mage::getSingleton('core/session')
                        ->addNotice(
                            $this->__('Your Google account is already connected to one of our store accounts.')
                        );

                    return;
                }

                // Connect from account dashboard - attach
                $customer = Mage::getSingleton('customer/session')->getCustomer();

                Mage::helper('ajaxlogin/google')->connectByGoogleId(
                    $customer,
                    $userInfo->id,
                    $token
                );

                Mage::getSingleton('core/session')->addSuccess(
                    $this->__('Your Google account is now connected to your store accout. You can now login using our Google Connect button or using store account credentials you will receive to your email address.')
                );

                return;
            }

            if($customersByGoogleId->count()) {
                // Existing connected user - login
                $customer = $customersByGoogleId->getFirstItem();

                Mage::helper('ajaxlogin/google')->loginByCustomer($customer);

                Mage::getSingleton('core/session')
                    ->addSuccess(
                        $this->__('You have successfully logged in using your Google account.')
                    );

                return;
            }

            $customersByEmail = Mage::helper('ajaxlogin/facebook')
                ->getCustomersByEmail($userInfo->email);

            if($customersByEmail->count())  {
                // Email account already exists - attach, login
                $customer = $customersByEmail->getFirstItem();
                
                Mage::helper('ajaxlogin/google')->connectByGoogleId(
                    $customer,
                    $userInfo->id,
                    $token
                );

                Mage::getSingleton('core/session')->addSuccess(
                    $this->__('We have discovered you already have an account at our store. Your Google account is now connected to your store account.')
                );

                return;
            }

            // New connection - create, attach, login
            if(empty($userInfo->given_name)) {
                throw new Exception(
                    $this->__('Sorry, could not retrieve your Google first name. Please try again.')
                );
            }

            if(empty($userInfo->family_name)) {
                throw new Exception(
                    $this->__('Sorry, could not retrieve your Google last name. Please try again.')
                );
            }

            Mage::helper('ajaxlogin/google')->connectByCreatingAccount(
                $userInfo->email,
                $userInfo->given_name,
                $userInfo->family_name,
                $userInfo->id,
                $token
            );

            Mage::getSingleton('core/session')->addSuccess(
                $this->__('Your Google account is now connected to your new user accout at our store. Now you can login using our Google Connect button or using store account credentials you will receive to your email address.')
            );
        }
    }

}