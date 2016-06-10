<?php
//maybe it should check if rfq or normal but not sure
    class ITwebexperts_Request4quote_Model_Cart extends Mage_Checkout_Model_Cart {

        protected function _getResource()
        {
            return Mage::getResourceSingleton('request4quote/cart');
        }

        public function getCheckoutSession()
        {
            return Mage::getSingleton('request4quote/session');
        }

        public function getCustomerSession()
        {
            return Mage::getSingleton('customer/session');
        }

        public function addProduct($productInfo, $requestInfo=null)
        {
            $product = $this->_getProduct($productInfo);
            $request = $this->_getProductRequest($requestInfo);

            $productId = $product->getId();

            /*if ($product->getStockItem()) {
                $minimumQty = $product->getStockItem()->getMinSaleQty();
                //If product was not found in cart and there is set minimal qty for it
                if ($minimumQty && $minimumQty > 0 && $request->getQty() < $minimumQty
                    && !$this->getQuote()->hasProductId($productId)
                ){
                    $request->setQty($minimumQty);
                }
            }*/

            if ($productId) {
                try {
                    $result = $this->getQuote()->addProduct($product, $request);
                } catch (Mage_Core_Exception $e) {
                    $this->getCheckoutSession()->setUseNotice(false);
                    $result = $e->getMessage();
                }
                /**
                 * String we can get if prepare process has error
                 */
                if (is_string($result)) {
                    $redirectUrl = ($product->hasOptionsValidationFail())
                        ? $product->getUrlModel()->getUrl(
                            $product,
                            array('_query' => array('startcustomization' => 1))
                        )
                        : $product->getProductUrl();
                    $this->getCheckoutSession()->setRedirectUrl($redirectUrl);
                    if ($this->getCheckoutSession()->getUseNotice() === null) {
                        $this->getCheckoutSession()->setUseNotice(true);
                    }
                    Mage::throwException($result);
                }
            } else {
                Mage::throwException(Mage::helper('checkout')->__('The product does not exist.'));
            }

            Mage::dispatchEvent('r4q_checkout_cart_product_add_after', array('quote_item' => $result, 'product' => $product));
            $this->getCheckoutSession()->setLastAddedProductId($productId);
            return $this;
        }


        public function updateItems($data)
        {
            //return parent::updateItems($data);
            Mage::dispatchEvent('r4q_checkout_cart_update_items_before', array('cart'=>$this, 'info'=>$data));

            /* @var $messageFactory Mage_Core_Model_Message */
            $messageFactory = Mage::getSingleton('core/message');
            $session = $this->getCheckoutSession();
            $qtyRecalculatedFlag = false;
            foreach ($data as $itemId => $itemInfo) {
                $item = $this->getQuote()->getItemById($itemId);
                if (!$item) {
                    continue;
                }

                if (!empty($itemInfo['remove']) || (isset($itemInfo['qty']) && $itemInfo['qty']=='0')) {
                    $this->removeItem($itemId);
                    continue;
                }

                $qty = isset($itemInfo['qty']) ? (float) $itemInfo['qty'] : false;
                if ($qty > 0) {
                    $item->setQty($qty);

                    $itemInQuote = $this->getQuote()->getItemById($item->getId());

                    if (!$itemInQuote && $item->getHasError()) {
                        Mage::throwException($item->getMessage());
                    }

                    if (isset($itemInfo['before_suggest_qty']) && ($itemInfo['before_suggest_qty'] != $qty)) {
                        $qtyRecalculatedFlag = true;
                        $message = $messageFactory->notice(Mage::helper('checkout')->__('Quantity was recalculated from %d to %d', $itemInfo['before_suggest_qty'], $qty));
                        $session->addQuoteItemMessage($item->getId(), $message);
                    }
                }
            }

            if ($qtyRecalculatedFlag) {
                $session->addNotice(
                    Mage::helper('checkout')->__('Some products quantities were recalculated because of quantity increment mismatch')
                );
            }

            Mage::dispatchEvent('r4q_checkout_cart_update_items_after', array('cart'=>$this, 'info'=>$data));
            return $this;
        }

        public function save()
        {
            Mage::dispatchEvent('r4q_checkout_cart_save_before', array('cart'=>$this));

            $this->getQuote()->getBillingAddress();
            $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->getQuote()->collectTotals();
            $this->getQuote()->save();
            $this->getCheckoutSession()->setQuoteId($this->getQuote()->getId());
            /**
             * Cart save usually called after changes with cart items.
             */
            Mage::dispatchEvent('r4q_checkout_cart_save_after', array('cart'=>$this));
            return $this;
        }

        public function getProductIds()
        {
            $quoteId = Mage::getSingleton('request4quote/session')->getQuoteId();
            if (null === $this->_productIds) {
                $this->_productIds = array();
               // if ($this->getSummaryQty()>0) {
                   foreach ($this->getQuote()->getAllItems() as $item) {
                       $this->_productIds[] = $item->getProductId();
                   }
                //}
                $this->_productIds = array_unique($this->_productIds);
            }
            return $this->_productIds;
        }

        public function getSummaryQty()
        {
            $quoteId = Mage::getSingleton('request4quote/session')->getQuoteId();

            //If there is no quote id in session trying to load quote
            //and get new quote id. This is done for cases when quote was created
            //not by customer (from backend for example).
            if (!$quoteId && Mage::getSingleton('customer/session')->isLoggedIn()) {
                $quote = Mage::getSingleton('request4quote/session')->getQuote();
                $quoteId = Mage::getSingleton('request4quote/session')->getQuoteId();
            }

            if ($quoteId && $this->_summaryQty === null) {
                if (Mage::getStoreConfig('checkout/cart_link/use_qty')) {
                    $this->_summaryQty = $this->getItemsQty();
                } else {
                    $this->_summaryQty = $this->getItemsCount();
                }
            }
            return $this->_summaryQty;
        }


        public function updateItem($itemId, $requestInfo = null, $updatingParams = null)
        {
            //return parent::updateItem($itemId, $requestInfo, $updatingParams);
            try {
                $item = $this->getQuote()->getItemById($itemId);
                if (!$item) {
                    Mage::throwException(Mage::helper('checkout')->__('Quote item does not exist.'));
                }
                $productId = $item->getProduct()->getId();
                $product = $this->_getProduct($productId);
                $request = $this->_getProductRequest($requestInfo);

                if ($product->getStockItem()) {
                    $minimumQty = $product->getStockItem()->getMinSaleQty();
                    // If product was not found in cart and there is set minimal qty for it
                    if ($minimumQty && ($minimumQty > 0)
                        && ($request->getQty() < $minimumQty)
                        && !$this->getQuote()->hasProductId($productId)
                    ) {
                        $request->setQty($minimumQty);
                    }
                }

                $result = $this->getQuote()->updateItem($itemId, $request, $updatingParams);
            } catch (Mage_Core_Exception $e) {
                $this->getCheckoutSession()->setUseNotice(false);
                $result = $e->getMessage();
            }

            /**
             * We can get string if updating process had some errors
             */
            if (is_string($result)) {
                if ($this->getCheckoutSession()->getUseNotice() === null) {
                    $this->getCheckoutSession()->setUseNotice(true);
                }
                Mage::throwException($result);
            }

            Mage::dispatchEvent('r4q_checkout_cart_product_update_after', array(
                'quote_item' => $result,
                'product' => $product
            ));
            $this->getCheckoutSession()->setLastAddedProductId($productId);
            return $result;
        }
    }