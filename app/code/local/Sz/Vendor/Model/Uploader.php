<?php
class Sz_Vendor_Model_Uploader extends Mage_Core_Model_Abstract
{
    CONST PENDING = 0;
    CONST WORKING = 1;
    CONST COMPLETE = 2;
    CONST ERROR = 3;
    public function _construct()
    {
        parent::_construct();
        $this->_init('vendor/uploader');
    }

    public function importProduts($fileName = null, $fileData= null) {
        try {
            $isPartner = Mage::getModel('vendor/userprofile')->isPartner(
                $fileData->getVendorId()
            );
            if (!is_null($fileName)) {
                $baseName = $fileName;
                $vendorHelper = Mage::helper('vendor');
                $fileName = $vendorHelper->getProductImportedFileDirectory().$fileName;
                if (!file_exists($fileName)) {
                    throw Mage::exception('File '.$baseName.' is not found');
                }
                if (!$isPartner) {
                    Mage::getSingleton('adminhtml/session')->addError(
                        'File '.$baseName.' associate with customer '. $fileData->getVendorId() .' is not an Vendor'
                    );
                    return;
                }
                if (($handle = fopen($fileName, "r")) !== FALSE) {
                    $headers = array_flip(fgetcsv($handle, 4086, ","));
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        if($data[0] == 'categories'){
                            continue;
                        }
                        $productData = array();
                        foreach ($headers as $key => $value) {
                            if (isset($data[$value])) {
                                $productData[$key] = $data[$value];
                            }
                        }
                        $this->createProduct($productData, $fileData);

                    }
                    $this->archiveExportedOrderFiles($fileName);
                    fclose($handle);
                }
            }
            return true;
        } catch (Exception $e) {
            throw Mage::exception($e->getMessage());
        }
    }
    
    protected function _getOptionIDByCode($attrCode, $optionLabel) 
    {
        $attrModel   = Mage::getModel('eav/entity_attribute');

        $attrID      = $attrModel->getIdByCode('catalog_product', $attrCode);
        $attribute   = $attrModel->load($attrID);

        $options     = Mage::getModel('eav/entity_attribute_source_table')
            ->setAttribute($attribute)
            ->getAllOptions(false);

        foreach ($options as $option) {
            if ($option['label'] == $optionLabel) {
                return $option['value'];
            }
        }

        return false;
    }
    public function createProduct($productData = array(), $fileData) {
        try {

            if (!is_null($productData)) {
                $magentoProductModel = Mage::getModel('catalog/product');
                if (isset($productData['sku']) && $productData['sku']) {
                    $isExist = $magentoProductModel->loadByAttribute('sku',$productData['sku']);
                    if (($isExist instanceof Mage_Catalog_Model_Product)
                        && $isExist->getId()) {
                        $magentoProductModel->setId($isExist->getId());
                    } else {
                        $magentoProductModel->setSku($productData['sku']);
                    }
                }
                $magentoProductModel->setWebsiteIds(array(1));
                $magentoProductModel->setAttributeSetId($fileData->getAttributeSet());
                $type = isset($productData['product_type'])?$productData['product_type']:'simple';
                $magentoProductModel->setTypeId($type);
                $magentoProductModel->setCreatedAt(strtotime('now'));
                if (isset($productData['product_name']) && $productData['product_name']) {
                    $magentoProductModel->setName($productData['product_name']);
                }
                if (isset($productData['description']) && $productData['description']) {
                    $magentoProductModel->setDescription($productData['description']);
                }
                if (isset($productData['short_description']) && $productData['short_description']) {
                    $magentoProductModel->setShortDescription($productData['short_description']);
                }
                if (isset($productData['price'])) {
                    $magentoProductModel->setPrice($productData['price']);
                }
                if (isset($productData['special_price']) && $productData['special_price']) {
                    $magentoProductModel->setSpecialPrice($productData['special_price']);
                }
                if (isset($productData['special_from_date']) && $productData['special_from_date']) {
                    $magentoProductModel->setSpecialFromDate($productData['special_from_date']);
                }
                if (isset($productData['special_to_date']) && $productData['special_to_date']) {
                    $magentoProductModel->setSpecialToDate($productData['special_to_date']);
                }
                if (isset($productData['weight']) && $productData['weight']) {
                    $magentoProductModel->setWeight($productData['weight']);
                }
                if (isset($productData['color']) && $productData['color']) {
                   $optionId = $this->_getOptionIDByCode('color',  $productData['color']);
                   $magentoProductModel->setColor($optionId);
                }
                if (isset($productData['accessories_size']) && $productData['accessories_size']) {
                    $optionId = $this->_getOptionIDByCode('accessories_size',  $productData['accessories_size']);
                    $magentoProductModel->setAccessoriesSize($optionId);
                }
                $magentoProductModel->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
                $magentoProductModel->setTaxClassId(0);
                if (isset($productData['categories']) && $productData['categories']) {
                    $magentoProductModel->setCategoryIds($this->_assignCategories($productData['categories']));
                }
                if ($type== 'configurable') {
                        $childProductsData = isset($productData['child_sku'])?$productData['child_sku']:'';
                        if ($childProductsData) {
                            $childSku = explode('|',$childProductsData);
                            if (is_array($childSku) && !empty($childSku)) {
                                $simpleProducts = Mage::getResourceModel('catalog/product_collection')
                                    ->addFieldToFilter('sku', array('In'=>$childSku))
                                    ->addAttributeToSelect('color')
                                    ->addAttributeToSelect('price');

                                $configurableProductsData = array();
                                $confAttributeArray = isset($productData['configurable_attributes'])?$productData['configurable_attributes']:'';
                                if ($confAttributeArray) {
                                    $confAttributeArray = explode('|',$confAttributeArray);
                                    $confAttribute = array();
                                    $confAttributes = array();
                                    foreach ($confAttributeArray as $confAttr) {
                                        $confAttribute[$confAttr] = Mage::getModel('eav/entity_attribute')->getIdByCode('catalog_product', $confAttr);
                                        $confAttributes[] =  $confAttribute[$confAttr];
                                    }
                                    $magentoProductModel->getTypeInstance()->setUsedProductAttributeIds($confAttributes);
                                    $configurableAttributesData = $magentoProductModel->getTypeInstance()->getConfigurableAttributesAsArray();
                                    foreach ($simpleProducts as $simple) {
                                        foreach ($confAttribute as $key=>$cfattr) {
                                            $productData = array(
                                                'label'         => $simple->getAttributeText($key),
                                                'attribute_id'  => $cfattr,
                                                'value_index'   => (int) $simple->getData($key),
                                                'is_percent'    => 0,
                                                'pricing_value' => $simple->getPrice()
                                            );
                                            $configurableProductsData[$simple->getId()] = $productData;
                                            $configurableAttributesData[0]['values'][] = $productData;
                                        }

                                    }

                                    $magentoProductModel->setConfigurableProductsData($configurableProductsData);
                                    $magentoProductModel->setConfigurableAttributesData($configurableAttributesData);
                                    $magentoProductModel->setCanSaveConfigurableAttributes(true);
                                    $magentoProductModel->save();
                                }
                            }
                        }


                }
                $saved =$magentoProductModel->save();
                $lastId = $saved->getId();
                if (isset($productData['stock']) && isset($productData['in_stock'])) {
                    $this->_saveStock(
                        $lastId,
                        $productData['stock'],
                        $productData['in_stock']
                    );
                }
                Mage::dispatchEvent('mp_customoption_setdata', array('id'=>$lastId));
                $collection1=Mage::getModel('vendor/product');
                $collection1->setmageproductid($lastId);
                $collection1->setuserid($fileData->getVendorId());
                $collection1->setstatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
                $collection1->save();
            }
        } catch (Exception $e) {
            //throw Mage::exception($e->getMessage());
        }
    }

    private function _createChildProduct($productData = array(),Mage_Catalog_Model_Product $magentoProductModel) {
        try {
            if (!empty($productData)) {
                $childCount = 1;
                foreach ($productData as $key =>$children) {
                    if (is_array($children) && !empty($children)) {
                        $simpleProduct = Mage::getModel('catalog/product');
                        $stockArray = array();
                        $isExist = $simpleProduct->loadByAttribute('sku', $magentoProductModel->getSku().'_'.$childCount);
                        if (($isExist instanceof Mage_Catalog_Model_Product)
                            && $isExist->getId()) {
                            $simpleProduct->setId($isExist->getId());
                        } else {
                            $simpleProduct->setSku($magentoProductModel->getSku().'_'.$childCount);
                        }
                        $simpleProduct->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
                        $simpleProduct->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
                        foreach ($children as $key=>$value) {
                            if ($key == 'qty') {
                                $stockArray = array (
                                    'stock'=>$value,
                                    'in_stock'=>1);
                            } else {
                                $simpleProduct->setData($key, $value);
                            }
                        }
                        $saved =$simpleProduct->save();
                        $lastId = $saved->getId();
                        if (!empty($stockArray)) {
                            $this->_saveStock($lastId, $stockArray['stock'],$stockArray['in_stock']);
                        }

                    }
                }
            }

        } catch (Exception $e) {
            throw Mage::exception($e->getMessage());
        }
    }

    private function _saveStock($lastId,$stock,$isstock){
        $stockItem = Mage::getModel('cataloginventory/stock_item');
        $stockItem->loadByProduct($lastId);
        if(!$stockItem->getId()){$stockItem->setProductId($lastId)->setStockId(1);}
        $stockItem->setProductId($lastId)->setStockId(1);
        $stockItem->setData('is_in_stock', $isstock);
        $savedStock = $stockItem->save();
        $stockItem->load($savedStock->getId())->setQty($stock)->save();
        // $qtyStock->setProductId($lastId)->setStockId(1);
        $stockItem->setData('is_in_stock', $isstock);
        $savedStock = $stockItem->save();
    }

    private function _assignCategories($categories = null) {
        try {
            if (!is_null($categories)) {
                $categoryArr = explode('|',$categories);
                $categoryIds = array();
                foreach($categoryArr as $categoryValue) {
                    $subcategory = explode('^',$categoryValue);
                    if (isset($subcategory[1]) && $subcategory[1]) {
                        $category = Mage::getResourceModel('catalog/category_collection')
                            ->addFieldToFilter('name', $subcategory[0])
                            ->getFirstItem();
                        $categoryIds[] = $category->getId();
                        $subCategory = Mage::getResourceModel('catalog/category_collection')
                            ->addFieldToFilter('name', $subcategory[1])
                            ->addFieldToFilter('parent_id',  $category->getId())->getFirstItem();
                        $categoryIds[] = $subCategory->getId();
                    } else {
                        $category = Mage::getResourceModel('catalog/category_collection')
                            ->addFieldToFilter('name', $subcategory[0])
                            ->getFirstItem();
                        $categoryIds[] = $category->getId();
                    }
                }
                return $categoryIds;
            }
            return array();
        } catch (Exception $e) {
            throw Mage::exception($e->getMessage());
        }
    }


    public function archiveExportedOrderFiles($fileName = null) {
        if (!is_null($fileName)) {
            $vendorHelper = Mage::helper('vendor');
            $archiveDir = $vendorHelper->getArchiveDirectory().basename($fileName);
            $io = new Varien_Io_File();
            $io->open(array('path' => $io->dirname($fileName)));
            $io->chmod($fileName,0777);
            try {
                if ($io->mv($fileName, $archiveDir)) {
                }
            } catch (exception $e) {
                throw Mage::exception($e->getMessage());
            }
        }
    }
}