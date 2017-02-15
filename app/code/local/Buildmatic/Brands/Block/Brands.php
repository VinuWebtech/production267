<?php
class Buildmatic_Brands_Block_Brands extends Mage_Core_Block_Template
{
    public function _prepareLayout() 
    {     
        $brand = Mage::getModel('buildmatic_brands/brands')->load($this->getRequest()->getParam('id'));    
        $head = $this->getLayout()->getBlock('head');
        $head->setTitle($brand->getMetaTitle());
        $head->setKeywords($brand->getMetaKeyword());
        $head->setDescription($brand->getMetaDescription());
        $head->addLinkRel('canonical', Mage::getBaseUrl().$brand->getUrlKey());
        return parent::_prepareLayout();
    }

	public function getBrand($brand_id)
	{
        if (Mage::registry('brand_data_id_collection'))
        {
            $brand = Mage::registry('brand_data_id_collection');
        }
        else
        {
            $brand = Mage::getSingleton('buildmatic_brands/brands')->load($brand_id);
            Mage::register('brand_data_id_collection', $brand);
        }
        return $brand;
	}

	public function getPriceHtml($product)
    {
        $this->setTemplate('brands/price.phtml');
        $this->setProduct($product);
        return $this->toHtml();
    }

    public function getBrandProductCollection($brands)

    {


            $category = Mage::getModel('catalog/category');

            $tree = $category->getTreeModel();

            $tree->load();

            $ids = $tree->getCollection()->getAllIds();

            if ($ids)

            {

                $new_id = 0;

                foreach ($ids as $id)

                {

                    $category->load($id);

                    if($category->getLevel()==2 && $category->getIsActive()==1)

                    {

                        $productCollection = $category->getProductCollection();

                        $attr = $productCollection->getResource()->getAttribute("manufacturer");

                        if ($attr->usesSource())

                        {

                            $manufacturer_id = $attr->getSource()->getOptionId($brands->getBrandName());

                        }

                        $productCollection = $productCollection->addAttributeToSelect('*')->addAttributeToFilter('manufacturer', $manufacturer_id);

                        if (!$_productCollection)

                        {

                            $_productCollection = $productCollection;

                        }

                        else

                        {

                            foreach ($productCollection as $item) 

                            {

                                $item->setId($new_id++);

                                $_productCollection->addItem($item);

                            }

                        } 

                    }

                }

                Mage::register('brand_product_collection', $_productCollection);

                var_dump(count($_productCollection));

            }

        

        return $_productCollection;

    }
}