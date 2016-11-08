<?php
class Magebright_Productrestriction_Model_Productrestriction extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('productrestriction/productrestriction');
    }
	public function updateOptionsToProductrestriction($collection,$options)
    {
        if($collection)
        { 
     
            foreach ($collection as $value)
            {
                $model = Mage::getModel('productrestriction/productrestriction');
                $data = $model->load($value['option_id']); 
             
                if(!$data->getProductrestrictionId())
                {  
                    
                    $model->setProductrestrictionId($value['option_id'])
                         ->setProductrestrictionOptionId($value['option_id'])
                         ->setName($value['value']) 
                         ->save();
                        
                } else {
                    
                    $data->setName($value['value'])
                         ->save();
                }                
            }
              
            foreach ($options['delete'] as $optionId => $value)
            { 
                if(!empty($value))
                {
                    $model->load($optionId);  
                    $image = $this->getImage();
                    $filepath = Mage::getBaseDir('media')."\productrestriction\\".$image;  
                    unlink($filepath);                    
                    $model->delete();
                }
            }
            
        }
    }
    
    
    public function attributeUpdate($option_id,$prodArray)
        {
           
            if (count($prodArray)>0) 
            {
                $attributeOptionArray=array();
                $opts_attr = Mage::getModel('eav/config')->getAttribute('catalog_product', 'productrestriction');
                foreach ( $opts_attr->getSource()->getAllOptions(true, true) as $option){
                    $attributeOptionArray[$option['value']] = $option['value'];
                }
               
                //$productrestriction_name =  Mage::getModel('productrestriction/productrestriction')->load($option_id)->getProductrestrictionName();
                $product_collection=Mage::getModel("productrestriction/productrestriction")->getCollection();
                $product_collection->addAttributeToFilter("productrestriction",array('finset'=>array_search($option_id,$attributeOptionArray)));
                $product_collection->addAttributeToFilter("entity_id",array('nin' => $prodArray));
                
                
                if(count($product_collection)>0)
                {

                    foreach($product_collection as $collections)
                    { 
                            $collections->setProductrestriction('');
                            $collections->save();
                    }
                }
                
                foreach($prodArray as $prodId)
                {
                    $pro = Mage::getModel('catalog/product')->load($prodId);  
                   
                        $this->productSet($pro,$option_id);   
                    
                }
            }
            else
            {
                $product_collection = $this->getFilterProducts('productrestriction',$option_id);                
                foreach($product_collection as $collections)
                {
                    $collections->setProductrestriction($option_id);
                    $collections->save();
                }
            }
        }

        /**
        * Retrieve array of Bundlecontent products
        *
        * @return array
        */
        public function getAttributeProducts()
        {
            if (!$this->hasAttributeProducts()) { 
                $products = array();
                $abc = $this->getAttributeProductCollection();
                $productsArr = $abc->getData();
                foreach ($productsArr as $key => $product) { 
                    $products[$product['product_id']] = $product['position']; 
                }   

                $this->setAttributeProducts($products); 
            }  
            return $this->getData('attribute_products');
        }

        /**
        * Retrieve Bundlecontent products identifiers
        *
        * @return array
        */
        public function getAttributeProductIds()
        {
            if (!$this->hasAttributeProductIds()) {

                $ids = array();
                foreach ($this->getAttributeProducts() as $product) {  

                    $ids[] = $product->getId(); 
                }
                $this->setAttributeProductIds($ids);
            }
            return $this->getData('attribute_product_ids'); 
        }

        /**
        * Retrieve collection of Attribute product   
        */
        public function getAttributeProductCollection()
        {           
            $collection = Mage::getModel('productrestriction/productrestriction')->getCollection()->addFieldToFilter('attribute_option_id',$this->getId());  
            return $collection;  
        }
        public function getFilterProducts($attribute,$attributeId)
        {
                $attributeOptionArray=array();
                $opts_attr = Mage::getModel('eav/config')->getAttribute('catalog_product', $attribute);
                foreach ( $opts_attr->getSource()->getAllOptions(true, true) as $option){
                    $attributeOptionArray[$option['value']] = $option['value'];
                }
             
                //$productrestriction_name =  Mage::getModel('productrestriction/productrestriction')->load($attributeId)->getProductrestrictionName();
                $product_collection=Mage::getModel("catalog/product")->getCollection();
                $product_collection->addAttributeToFilter("productrestriction",array('finset'=>array_search($attributeId,$attributeOptionArray)));
                return $product_collection;
           
        }
        public function productSet($pro,$option_id)
        {
            $pro->setProductrestriction($option_id);
            $pro->save();
        }
     
}