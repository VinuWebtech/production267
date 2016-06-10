<?php

if (Mage::helper('request4quote')->isRentalInstalled()) {

    class ITwebexperts_Request4quote_Model_Sales_Quote_Address_Total_Subtotal extends ITwebexperts_Payperrentals_Model_Sales_Quote_Address_Total_Subtotal
    {

        protected function _initItem($address, $item)
        {
            //if (method_exists($address->getQuote(), 'isR4q') && $address->getQuote()->isR4q()) {
            parent::_initItem($address, $item);
            if ($item instanceof Mage_Sales_Model_Quote_Address_Item) {
                $quoteItem = $item->getAddress()->getQuote()->getItemById($item->getQuoteItemId());
            } else {
                $quoteItem = $item;
            }
            if (!$quoteItem->getParentItem()) {
                $source = unserialize($quoteItem->getProduct()->getCustomOption('info_buyRequest')->getValue());
                $finalPrice = false;
                if (isset($source['r4q_price_proposal'])) {
                    $priceProposal = floatval($source['r4q_price_proposal']);
                    $finalPrice = Mage::helper("tax")->getPrice($quoteItem->getProduct(), $priceProposal);
                }else if($item->getR4qPriceProposal() !== false && !is_null($item->getR4qPriceProposal())){
                    $priceProposal = floatval($item->getR4qPriceProposal());
                    $finalPrice = Mage::helper("tax")->getPrice($quoteItem->getProduct(), $priceProposal);
                }
                if($finalPrice !== false) {
                    $item->setPrice($finalPrice)
                        ->setBaseOriginalPrice($finalPrice);
                    $item->setCustomPrice($finalPrice);
                    $item->setOriginalCustomPrice($finalPrice);
                    $item->getProduct()->setIsSuperMode(true);
                    $item->calcRowTotal();
                }
            }
            return true;
            //}else{
            //  parent::_initItem($address, $item);
//                    return true;
            //              }
        }

    }


} else {

    class ITwebexperts_Request4quote_Model_Sales_Quote_Address_Total_Subtotal extends Mage_Sales_Model_Quote_Address_Total_Subtotal
    {

        protected function _initItem($address, $item)
        {
            //    if (method_exists($address->getQuote(), 'isR4q') && $address->getQuote()->isR4q()) {
            parent::_initItem($address, $item);
            if ($item instanceof Mage_Sales_Model_Quote_Address_Item) {
                $quoteItem = $item->getAddress()->getQuote()->getItemById($item->getQuoteItemId());
            } else {
                $quoteItem = $item;
            }
            if (!$quoteItem->getParentItem() && is_object($quoteItem->getProduct()) && is_object($quoteItem->getProduct()->getCustomOption('info_buyRequest'))) {
                $source = unserialize($quoteItem->getProduct()->getCustomOption('info_buyRequest')->getValue());
                $finalPrice = false;
                if (isset($source['r4q_price_proposal'])) {
                    $priceProposal = floatval($source['r4q_price_proposal']);
                    if (Mage::helper('request4quote')->isTaxEstimatesEnabled() == true) {
                        $finalPrice = Mage::helper("tax")->getPrice($quoteItem->getProduct(), $priceProposal);
                    }else{
                        $finalPrice = $priceProposal;
                    }
                }else if($item->getR4qPriceProposal() !== false && !is_null($item->getR4qPriceProposal())){
                    $priceProposal = floatval($item->getR4qPriceProposal());
                    if (Mage::helper('request4quote')->isTaxEstimatesEnabled() == true) {
                        $finalPrice = Mage::helper("tax")->getPrice($quoteItem->getProduct(), $priceProposal);
                    }else{
                        $finalPrice = $priceProposal;
                    }
                }

                if($finalPrice !== false) {
                    $item->setPrice($finalPrice)
                        ->setBaseOriginalPrice($finalPrice);
                    $item->setCustomPrice($finalPrice);
                    $item->setOriginalCustomPrice($finalPrice);
                    $item->setNoDiscount(1);
                    $item->getProduct()->setIsSuperMode(true);
                    $item->calcRowTotal();
                }
            }

            return true;
            // }else{
            //        parent::_initItem($address, $item);
            //        return true;
            //     }
        }

    }
}

