    <?php
   
    class Magebright_Productrestriction_Block_Adminhtml_Productrestriction_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
    {
     
        public function __construct()
        {
            parent::__construct();
            $this->setId('productrestriction_tabs');
            $this->setDestElementId('edit_form');
            $this->setTitle(Mage::helper('productrestriction')->__('Productrestriction Information'));
        }
     
        protected function _beforeToHtml()
        {
            $this->addTab('form_section', array(
                'label'     => Mage::helper('productrestriction')->__('Productrestriction Information'),
                'title'     => Mage::helper('productrestriction')->__('Productrestriction Information'),
                'content'   => $this->getLayout()->createBlock('productrestriction/adminhtml_productrestriction_edit_tab_form')->toHtml(),
            ));
            
             $this->addTab('grid_section', array(
            'label'     => Mage::helper('productrestriction')->__('Product'),
            'title'     => Mage::helper('productrestriction')->__('Product'),
            'url'       => $this->getUrl('*/*/product', array('_current' => true)),
            'class'     => 'ajax',
            ));
            
            
            return parent::_beforeToHtml();
        }
    }