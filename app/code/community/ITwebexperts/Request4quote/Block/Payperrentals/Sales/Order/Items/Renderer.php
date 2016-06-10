<?php

    class ITwebexperts_Request4quote_Block_Payperrentals_Sales_Order_Items_Renderer extends ITwebexperts_Payperrentals_Block_Sales_Order_Items_Renderer {
        public function getOrderItem()
        {
            $item = $this->getItem();
            if ($item->getBuyRequest()) {
                $item->setProductOptions(array('info_buyRequest' => $item->getBuyRequest()->toArray()));
            }
            return $this->getItem();
        }
    }
