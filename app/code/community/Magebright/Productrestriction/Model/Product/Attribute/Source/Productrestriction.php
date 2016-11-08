 <?php
class Magebright_Productrestriction_Model_Product_Attribute_Source_Productrestriction extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options =   array('value'=>0, 'label'=>Mage::helper('catalog')->__('No Zipcodeck'));
        }
        $data = Mage::getModel('productrestriction/productrestriction')->getCollection();
     	if($data) {
     		  $this->_options = array(); 
     	
	     	 foreach ($data as $attribute) { 
	            $this->_options[] = array(
		                'label' => Mage::helper('productrestriction')->__($attribute['name']),
		                'value' => $attribute['productrestriction_id'] 
		            ); 
	     	 }
        } 
        return $this->_options;
    }
}
