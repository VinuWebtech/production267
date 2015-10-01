<?php
class Sz_Ajaxlogin_Model_Facebook_Userinfo
{
    protected $client = null;
    protected $userInfo = null;

    public function __construct() {
        if(!Mage::getSingleton('customer/session')->isLoggedIn())
            return;

        $this->client = Mage::getSingleton('sz_ajaxlogin/facebook_client');
        if(!($this->client->isEnabled())) {
            return;
        }

        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if(($socialconnectFid = $customer->getInchooSocialconnectFid()) &&
                ($socialconnectFtoken = $customer->getInchooSocialconnectFtoken())) {
            $helper = Mage::helper('sz_ajaxlogin/facebook');

            try{
                $this->client->setAccessToken($socialconnectFtoken);
                $this->userInfo = $this->client->api(
                    '/me',
                    'GET',
                    array(
                        'fields' =>
                        'id,name,first_name,last_name,link,birthday,gender,email,picture.type(large)'
                    )
                );

            } catch(FacebookOAuthException $e) {
                $helper->disconnect($customer);
                Mage::getSingleton('core/session')->addNotice($e->getMessage());
            } catch(Exception $e) {
                $helper->disconnect($customer);
                Mage::getSingleton('core/session')->addError($e->getMessage());
            }

        }
    }

    public function getUserInfo()
    {
        return $this->userInfo;
    }
}