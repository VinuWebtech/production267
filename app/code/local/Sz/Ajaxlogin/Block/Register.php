<?php
class  Sz_Ajaxlogin_Block_Register extends Mage_Core_Block_Template
{
    protected $clientGoogle = null;
    protected $clientFacebook = null;
    
    protected $numEnabled = 0;
    protected $numShown = 0;

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

        Mage::register('sz_ajaxlogin_button_text', $this->__('Register'));

        $this->setTemplate('ajaxlogin/socialconnect/register.phtml');
    }

    protected function _getColSet()
    {
        return 'col'.$this->numEnabled.'-set';
    }

    protected function _getCol()
    {
        return 'col-'.++$this->numShown;
    }

    protected function _googleEnabled()
    {
        return (bool) $this->clientGoogle->isEnabled();
    }

    protected function _facebookEnabled()
    {
        return (bool) $this->clientFacebook->isEnabled();
    }



}