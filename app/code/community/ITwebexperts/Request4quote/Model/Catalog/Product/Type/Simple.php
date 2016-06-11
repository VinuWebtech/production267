<?php

class ITwebexperts_Request4quote_Model_Catalog_Product_Type_Simple extends Mage_Catalog_Model_Product_Type_Simple {

    protected function _prepareOptions(Varien_Object $buyRequest, $product, $processMode)
    {
        $transport = new StdClass;
        $transport->options = array();
        foreach ($this->getProduct($product)->getOptions() as $_option) {
            /* @var $_option Mage_Catalog_Model_Product_Option */
            $group = $_option->groupFactory($_option->getType())
                ->setOption($_option)
                ->setProduct($this->getProduct($product))
                ->setRequest($buyRequest)
                ->setProcessMode($processMode);
            if(!$buyRequest->getData('r4q_quote_id')){
                $group->validateUserValue($buyRequest->getOptions());
            }

            $preparedValue = $group->prepareForCart();
            if ($preparedValue !== null) {
                $transport->options[$_option->getId()] = $preparedValue;
            }
        }

        $eventName = sprintf('catalog_product_type_prepare_%s_options', $processMode);
        Mage::dispatchEvent($eventName, array(
            'transport'   => $transport,
            'buy_request' => $buyRequest,
            'product' => $product
        ));
        return $transport->options;
    }

}