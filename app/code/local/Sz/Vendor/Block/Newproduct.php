<?php
class Sz_Vendor_Block_Newproduct extends Mage_Customer_Block_Account_Dashboard
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
    public function getAllowedSets(){
		$entityTypeId = Mage::getModel('eav/entity')
                ->setType('catalog_product')
                ->getTypeId();
        $data=array();
        $allowed=explode(',',Mage::getStoreConfig('vendor/vendor_options/attributesetid'));
        $attributeSetCollection = Mage::getModel('eav/entity_attribute_set')
                        ->getCollection()
                        ->addFieldToFilter('attribute_set_id',array('in'=>$allowed))
                        ->setEntityTypeFilter($entityTypeId);
        foreach($attributeSetCollection as $_attributeSet){
            array_push($data,array('value'=>$_attributeSet->getData('attribute_set_id'), 'label'=>$_attributeSet->getData('attribute_set_name')));
        }
        return $data;
	}

	public function getAllowedProductTypes(){
		$alloweds=explode(',',Mage::getStoreConfig('vendor/vendor_options/allow_for_vendor'));
		$data =  array('simple'=>'Simple',
						'downloadable'=>'Downloadable',
					    'virtual'=>'Virtual',
						'configurable'=>'Configurable',
						'grouped'=>'Grouped Product',
						'bundle'=>'Bundle Product'
			);
		$allowedproducts=array();
		foreach($alloweds as $allowed){
			array_push($allowedproducts,array('value'=>$allowed, 'label'=>$data[$allowed]));
		}
		return $allowedproducts;
	}
}
