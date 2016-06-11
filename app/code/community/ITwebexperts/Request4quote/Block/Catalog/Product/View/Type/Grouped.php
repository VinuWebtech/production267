<?php

class ITwebexperts_Request4quote_Block_Catalog_Product_View_Type_Grouped extends Mage_Catalog_Block_Product_View_Type_Grouped
{
    public function getPriceHtml($product, $displayMinimalPrice = false, $idSuffix = '')
    {
        $r4qEnabled= Mage::helper('itwebcommon')->getAttributeCodeForId($product->getId(),'r4q_enabled');
        if($r4qEnabled && Mage::helper('request4quote')->isPriceHidden($product)){
            return '';
        }
        return parent::getPriceHtml($product, $displayMinimalPrice, $idSuffix);
    }
}
