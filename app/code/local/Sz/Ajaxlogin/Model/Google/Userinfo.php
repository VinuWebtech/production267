<?php
class Sz_Ajaxlogin_Model_Google_Userinfo
{
    protected $client = null;
    protected $userInfo = null;

    public function __construct() {
        if(!Mage::getSingleton('customer/session')->isLoggedIn())
            return;

        $this->client = Mage::getSingleton('sz_ajaxlogin/google_client');
        if(!($this->client->isEnabled())) {
            return;
        }

        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if(($socialconnectGid = $customer->getInchooSocialconnectGid()) &&
                ($socialconnectGtoken = $customer->getInchooSocialconnectGtoken())) {
            $helper = Mage::helper('sz_ajaxlogin/google');

            try{
                $this->client->setAccessToken($socialconnectGtoken);

                $this->userInfo = $this->client->api('/userinfo');

                /* The access token may have been updated automatically due to
                 * access type 'offline' */
                $customer->setInchooSocialconnectGtoken($this->client->getAccessToken());
                $customer->save();

            } catch(Inchoo_SocialConnect_GoogleOAuthException $e) {
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