<?php
class Buildmatic_Brands_Block_Brands extends Mage_Core_Block_Template
{
    protected $_defaultToolbarBlock = 'catalog/product_list_toolbar';

    protected $_productCollection;

    public function _prepareLayout() 
    {     
        $brand = Mage::getModel('buildmatic_brands/brands')->load($this->getRequest()->getParam('id'));    
        $head = $this->getLayout()->getBlock('head');
        $head->setTitle($brand->getMetaTitle());
        $head->setKeywords($brand->getMetaKeyword());
        $head->setDescription($brand->getMetaDescription());
        $head->addLinkRel('canonical', Mage::getBaseUrl().Mage::getStoreConfig('brands_section/brands_settings/url_prefix').'/'.$brand->getUrlKey());

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

    public function getBrandProductCollection($brand)
    {
        $layer = $this->getLayer();
        $category = Mage::getModel('catalog/category');
        $tree = $category->getTreeModel();
        $tree->load();
        $ids = $tree->getCollection()->getAllIds();
        if ($ids)
        {
            $_productCollection = array();
            foreach ($ids as $id)
            {
                $category->load($id);
                if($category->getLevel()==2 && $category->getIsActive()==1)
                {
                    $productCollection = $category->getProductCollection();
                    $attr = $productCollection->getResource()->getAttribute("manufacturer");
                    if ($attr->usesSource())
                    {
                        $manufacturer_id = $attr->getSource()->getOptionId($brand->getBrandName());
                    }
                    $productCollection = $productCollection->addAttributeToSelect('*')->addAttributeToFilter('manufacturer', $manufacturer_id);
                    if (count($_productCollection) == 0)
                    {
                        $_productCollection = $productCollection;
                    }
                    else
                    {
                        foreach ($productCollection as $item) 
                        {
                            $_productCollection->addItem($item);
                        }
                    }
                }
            }  
        }
        //var_dump(count($_productCollection));
        return $_productCollection;
    }

    public function getCatProduct($category,$brand)
    {
        $layer = $this->getLayer();
        $collection = Mage::getModel('catalog/category')
                    ->load($category)
                    ->getProductCollection();

        $attr = $collection->getResource()->getAttribute("manufacturer");
        if ($attr->usesSource()) 
        {
            $manufacturer_id = $attr->getSource()->getOptionId($brand->getBrandName());
        }
        $collection = $collection->addAttributeToSelect('*')->addAttributeToFilter('manufacturer', $manufacturer_id); 
        return $collection;
    }

    protected function _beforeToHtml()
    {
        $toolbar = $this->getToolbarBlock();
        //var_dump($toolbar);
        
        // called prepare sortable parameters
        $collection = $this->_getProductCollection();

        // use sortable parameters
        if ($orders = $this->getAvailableOrders()) 
        {
            $toolbar->setAvailableOrders($orders);
        }
        if ($sort = $this->getSortBy()) 
        {
            $toolbar->setDefaultOrder($sort);
        }
        if ($dir = $this->getDefaultDirection()) 
        {
            $toolbar->setDefaultDirection($dir);
        }
        if ($modes = $this->getModes()) 
        {
            $toolbar->setModes($modes);
        }

        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection->clear());

        $this->setChild('toolbar', $toolbar);
        Mage::dispatchEvent('catalog_block_product_list_collection', array(
            'collection' => $collection
        ));

        //$collection->load();

        return parent::_beforeToHtml();
    }

    public function getToolbarBlock()
    {
        if ($blockName = $this->getToolbarBlockName()) 
        {
            if ($block = $this->getLayout()->getBlock($blockName)) 
            {
                return $block;
            }
        }
        $block = $this->getLayout()->createBlock($this->_defaultToolbarBlock, microtime());
        return $block;
    }

    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }

    public function getMode()
    {
        return $this->getChild('toolbar')->getCurrentMode();
    }

    public function setCollection($collection)
    {
        $this->_productCollection = $collection;
        return $this;
    }

    public function getLayer()
    {
        $layer = Mage::registry('current_layer');
        if ($layer) {
            return $layer;
        }
        return Mage::getSingleton('catalog/layer');
    }

    public function getLoadedProductCollection()
    {
        return $this->_getProductCollection();
    }

    protected function _getProductCollection()
    {
        if (is_null($this->_productCollection)) 
        {
            $layer = $this->getLayer();
            $request = Mage::app()->getRequest();
            $parts = explode('/', $request->getPathInfo());
            foreach ($parts as $part) 
            {
                if ($part != "") 
                {
                    $catName = $part;
                }
            }
            $brand = $this->getBrand($this->getRequest()->getParam('id'));
            if ($catName == $brand->getUrlKey())
            {
                $this->_productCollection = $this->getBrandProductCollection($brand);
            }
            else
            {
                $category = Mage::getModel('catalog/category')->getCollection()->addAttributeToSelect('*')->addAttributeToFilter('url_key', $catName);
                $data = $category->getData();
                $data = $data['0']['entity_id'];

                $this->_productCollection = $this->getCatProduct($data,$brand);
            }
        }
        return $this->_productCollection;
    }
}