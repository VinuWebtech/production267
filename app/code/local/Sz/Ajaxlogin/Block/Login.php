<?php
class Sz_Ajaxlogin_Block_Login extends Mage_Core_Block_Template
{
    protected $clientGoogle = null;
    protected $clientFacebook = null;

    protected $numEnabled = 0;
    protected $numDescShown = 0;
    protected $numButtShown = 0;

    protected function _construct() {
        parent::_construct();

        $this->clientGoogle = Mage::getSingleton('ajaxlogin/google_client');
        $this->clientFacebook = Mage::getSingleton('ajaxlogin/facebook_client');

        if($this->_googleEnabled()) {
            $this->numEnabled++;
        }

        if($this->_facebookEnabled()) {
            $this->numEnabled++;
        }

        Mage::register('sz_ajaxlogin_button_text1', $this->__('Login'));

        $this->setTemplate('ajaxlogin/socialconnect/login.phtml');
    }

    protected function _getColSet()
    {
        return 'col'.$this->numEnabled.'-set';
    }

    protected function _getDescCol()
    {
        return 'col-'.++$this->numDescShown;
    }

    protected function _getButtCol()
    {
        return 'col-'.++$this->numButtShown;
    }

    protected function _googleEnabled()
    {
        return $this->clientGoogle->isEnabled();
    }

    protected function _facebookEnabled()
    {
        return $this->clientFacebook->isEnabled();
    }



}
