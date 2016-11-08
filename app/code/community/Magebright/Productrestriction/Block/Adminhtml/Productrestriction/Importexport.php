<?php
class Magebright_Productrestriction_Block_Adminhtml_Productrestriction_Importexport extends Mage_Adminhtml_Block_Template
{
   
	
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'productrestriction';
        $this->_controller = 'adminhtml_productrestriction';
        $this->_mode        = 'view'; 

        parent::__construct();
    }
     
    
    protected function _prepareLayout()
    {  
    	 $backButton = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'     => Mage::helper('productrestriction')->__('Back'),
                'onclick'   => "setLocation('".$this->getBackUrl()."')",
                'class'     => 'back'
            ));

        $this->setChild('back_button', $backButton);
        
        return parent::_prepareLayout();
    }
    
    
    public function getInstantcoupon()
    {
    	if($id = $this->getRequest()->getParam('id'))
    	{
    		$crData = Mage::getModel('productrestriction/productrestriction')->load($id);
    		if(!empty($crData))
    		{
    			return $crData;
    		}
    	} 
    	
    } 
    
    /**
     * Return back url for view grid
     *
     * @return string
     */
    public function getBackUrl()
    { 
        return $this->getUrl('*/*/');
    }

}
