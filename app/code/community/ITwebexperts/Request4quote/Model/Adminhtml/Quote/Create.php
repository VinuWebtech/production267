<?php
if(Mage::helper('request4quote')->hasAdminShipping()){
    class ITwebexperts_Request4quote_Model_Adminhtml_Quote_Create_Common extends ITwebexperts_AdminShipping_Model_Sales_Order_Create{

    }
}else{
    class ITwebexperts_Request4quote_Model_Adminhtml_Quote_Create_Common extends Mage_Adminhtml_Model_Sales_Order_Create{

    }
}
    class ITwebexperts_Request4quote_Model_Adminhtml_Quote_Create extends ITwebexperts_Request4quote_Model_Adminhtml_Quote_Create_Common
    {

        /**
         * Parse data retrieved from request
         *
         * @param   array $data
         * @return  Mage_Adminhtml_Model_Sales_Order_Create
         */
        public function importPostData($data)
        {
            if (is_array($data)) {
                $this->addData($data);
            } else {
                return $this;
            }

            if (isset($data['account'])) {
                $this->setAccountData($data['account']);
            }

            if (isset($data['comment'])) {
                $this->getQuote()->addData($data['comment']);
                if (empty($data['comment']['customer_note_notify'])) {
                    $this->getQuote()->setCustomerNoteNotify(false);
                } else {
                    $this->getQuote()->setCustomerNoteNotify(true);
                }
            }

            if (isset($data['billing_address'])) {
                $this->setBillingAddress($data['billing_address']);
            }

            if (isset($data['shipping_address'])) {
                $this->setShippingAddress($data['shipping_address']);
            }

            if (isset($data['shipping_method'])) {
                $this->setShippingMethod($data['shipping_method']);
            }

            if (isset($data['payment_method'])) {
                $this->setPaymentMethod($data['payment_method']);
            }

            if($this->getShippingMethod() && $this->getShippingMethod() == 'adminshipping_adminshipping') {
                if (isset($data['shipping_amount'])) {
                    $shippingPrice = $this->_parseShippingPrice($data['shipping_amount']);
                    $this->getQuote()->setCustomPrice($shippingPrice);
                }

                if (isset($data['shipping_description'])) {
                    $this->getQuote()->setCustomTitle($data['shipping_description']);
                }

                if ($this->getQuote()->getCustomPrice()) {
                    $shippingPrice = $this->_parseShippingPrice($this->getQuote()->getCustomPrice());
                    $this->getQuote()->getShippingAddress()->setShippingAmount($shippingPrice);
                }

                if ($this->getQuote()->getCustomPrice()) {
                    $baseShippingPrice = $this->_parseShippingPrice($this->getQuote()->getCustomPrice());
                    $this->getQuote()->getShippingAddress()->setBaseShippingAmount($baseShippingPrice, true);
                }

                if ($this->getQuote()->getCustomTitle()) {
                    $this->getQuote()->getShippingAddress()->setShippingDescription($this->getQuote()->getCustomTitle());
                }

            }

            if ($this->getQuote()->getShippingAddress()->getShippingAmount()) {
                $this->getQuote()->setCustomPrice($this->getQuote()->getShippingAddress()->getShippingAmount());
            }

            if ($this->getQuote()->getShippingAddress()->getShippingDescription()) {
                $this->getQuote()->setCustomTitle($this->getQuote()->getShippingAddress()->getShippingDescription());
            }
            if (isset($data['coupon']['code'])) {
                $this->applyCoupon($data['coupon']['code']);
            }
            return $this;
        }

        protected function _parseShippingPrice($price)
        {
            $price = Mage::app()->getLocale()->getNumber($price);
            $price = $price>0 ? $price : 0;
            return $price;
        }
        public function updateQuoteItems($data)
        {
            if (is_array($data)) {
                try {
                    foreach ($data as $itemId => $info) {
                        if (!empty($info['configured'])) {
                            $item = $this->getQuote()->updateItem($itemId, new Varien_Object($info));
                            $itemQty = (float)$item->getQty();
                        } else {
                            $item = $this->getQuote()->getItemById($itemId);
                            $itemQty = (float)$info['qty'];
                        }

                        if ($item) {
                            if ($item->getProduct()->getStockItem()) {
                                if (!$item->getProduct()->getStockItem()->getIsQtyDecimal()) {
                                    $itemQty = (int)$itemQty;
                                } else {
                                    $item->setIsQtyDecimal(1);
                                }
                            }
                            $itemQty = $itemQty > 0 ? $itemQty : 1;
                            if (isset($info['custom_price'])) {
                                $itemPrice = $this->_parseCustomPrice($info['custom_price']);
                            } else {
                                $itemPrice = null;
                            }
                            $noDiscount = !isset($info['use_discount']);

                            if (empty($info['action']) || !empty($info['configured'])) {
                                if (isset($info['r4q_price_proposal'])) {
                                    $item->setR4qPriceProposal((float)$info['r4q_price_proposal']);
                                }
                                if (isset($info['r4q_note'])) {
                                    $item->setR4qNote($info['r4q_note']);
                                }
                                $item->setQty($itemQty);
                                $item->setCustomPrice($itemPrice);
                                $item->setOriginalCustomPrice($itemPrice);
                                $item->setNoDiscount($noDiscount);
                                $item->getProduct()->setIsSuperMode(true);
                                $item->getProduct()->unsSkipCheckRequiredOption();
                                $item->checkData();
                            } else {
                                $this->moveQuoteItem($item->getId(), $info['action'], $itemQty);
                            }
                        }
                    }
                } catch (Mage_Core_Exception $e) {
                    $this->recollectCart();
                    throw $e;
                } catch (Exception $e) {
                    Mage::logException($e);
                }
                $this->recollectCart();
            }
            return $this;
        }

        public function __construct()
        {
            $this->_session = Mage::getSingleton('request4quote/adminhtml_session_quote');
        }

        public function getSession()
        {
            return Mage::getSingleton('request4quote/adminhtml_session_quote');
        }

        public function getQuote()
        {
            return Mage::getSingleton('request4quote/adminhtml_session_quote')->getQuote();
        }

        public function getCustomerCart()
        {
            if (!is_null($this->_cart)) {
                return $this->_cart;
            }

            $this->_cart = Mage::getModel('request4quote/quote');

            if ($this->getSession()->getCustomer()->getId()) {
                $this->_cart->setStore($this->getSession()->getStore())
                    ->loadByCustomer($this->getSession()->getCustomer()->getId());
                if (!$this->_cart->getId()) {
                    $this->_cart->assignCustomer($this->getSession()->getCustomer());
                    $this->_cart->save();
                }
            }

            return $this->_cart;
        }

        public function createOrder()
        {
            Mage::throwException(Mage::helper('request4quote')->__('Unable to create order from quote request.'));
        }

        public function saveCustomer()
        {
            $quote = $this->getQuote();
            if ($quote->getCustomerIsGuest()) {
                return $this;
            }
            $customer = $this->getSession()->getCustomer();
            $store = $this->getSession()->getStore();
            $websiteId = $store->getWebsiteId();
            $accountData = $this->getData('account');
            try {
                //if (!$customer->getId()) {
                    if(isset($accountData['email'])) {
                        $customerModel = Mage::getModel('customer/customer');
                        $customerClass = $customerModel->setWebsiteId($websiteId)->loadByEmail($accountData['email']);
                        if(!($customerClass->getId())) {
                            $appEmulation = Mage::getSingleton('core/app_emulation');
                            $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($store->getId());


                            $customerModel->setWebsiteId($websiteId);
                            $customerModel->setStore($store);
                            $customerModel->setData($accountData);
                            $customerModel->setPassword($customer->generatePassword());
                            $customerModel->save();
                            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                        }
                    }

                //}
            } catch (Exception $e) {
            }
            if(isset($accountData['email'])) {
                $customerModel = Mage::getModel('customer/customer');
                $customerClass = $customerModel->setWebsiteId($websiteId)->loadByEmail($accountData['email']);
                $this->setCustomer($customerClass);
            }
            return $this;
        }


        public function applySidebarData($data)
        {
            if (isset($data['add_cart_item'])) {
                foreach ($data['add_cart_item'] as $itemId => $qty) {
                    $item = $this->getCustomerCart()->getItemById($itemId);
                    if ($item) {
                        $this->moveQuoteItem($item, 'order', $qty);
                        $this->removeItem($itemId, 'cart');
                    }
                }
            }
            if (isset($data['add_wishlist_item'])) {
                foreach ($data['add_wishlist_item'] as $itemId => $qty) {
                    $item = Mage::getModel('wishlist/item')
                        ->loadWithOptions($itemId, 'info_buyRequest');
                    if ($item->getId()) {
                        $this->addProduct($item->getProduct(), $item->getBuyRequest()->toArray());
                    }
                }
            }
            if (isset($data['add'])) {
                foreach ($data['add'] as $productId => $qty) {
                    $this->addProduct($productId, array('qty' => $qty));
                }
            }
            if (isset($data['remove'])) {
                foreach ($data['remove'] as $itemId => $from) {
                    $this->removeItem($itemId, $from);
                }
            }
            if (isset($data['empty_customer_cart']) && (int)$data['empty_customer_cart'] == 1) {
                $this->getCustomerCart()->removeAllItems()->collectTotals()->save();
            }
            return $this;
        }


        public function setShippingAddress($address)
        {
            if (is_array($address)) {
                $address['save_in_address_book'] = isset($address['save_in_address_book'])
                    && !empty($address['save_in_address_book']);
                $shippingAddress = Mage::getModel('request4quote/quote_address')
                    ->setData($address)
                    ->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_SHIPPING);
                if (!$this->getQuote()->isVirtual()) {
                    $this->_setQuoteAddress($shippingAddress, $address);
                }
                $shippingAddress->implodeStreetAddress();
            }
            if ($address instanceof Mage_Sales_Model_Quote_Address) {
                $shippingAddress = $address;
            }

            $this->setRecollect(true);
            $this->getQuote()->setShippingAddress($shippingAddress);
            return $this;
        }


        public function setBillingAddress($address)
        {
            if (is_array($address)) {
                $address['save_in_address_book'] = isset($address['save_in_address_book']) ? 1 : 0;
                $billingAddress = Mage::getModel('request4quote/quote_address')
                    ->setData($address)
                    ->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_BILLING);
                $this->_setQuoteAddress($billingAddress, $address);
                $billingAddress->implodeStreetAddress();
            }

            if ($this->getShippingAddress()->getSameAsBilling()) {
                $shippingAddress = clone $billingAddress;
                $shippingAddress->setSameAsBilling(true);
                $shippingAddress->setSaveInAddressBook(false);
                $address['save_in_address_book'] = 0;
                $this->setShippingAddress($address);
            }

            $this->getQuote()->setBillingAddress($billingAddress);
            return $this;
        }

        private function _getCustomerIdByEmail($email, $storeId)
        {
            $customer = Mage::getModel('customer/customer');
            $customer->setWebsiteId($storeId);
            $customer->loadByEmail($email);
            return (is_null($customer->getId())?false:$customer->getId());
        }


        /**
         * Initialize creation data from existing order
         *
         * @param Mage_Sales_Model_Order $order
         *
         * @return Innoexts_Warehouse_Model_Adminhtml_Sales_Order_Create
         */
        public function initFromQuote($quote)
        {

            $this->getSession()->setQuoteId($quote->getId());
            //$this->getSession()->setCurrencyId($order->getOrderCurrencyCode());

            if ($quote->getCustomerEmail()) {
                $this->getSession()->setCustomerId($this->_getCustomerIdByEmail($quote->getCustomerEmail(), $quote->getStoreId()));
            } else {
                $this->getSession()->setCustomerId(false);
            }
            $this->getSession()->setStoreId($quote->getStoreId());

            // $this->initRuleData();
            $availableProductTypes = Mage::getConfig()->getNode('adminhtml/sales/order/create/available_product_types')->asArray();
            foreach ($quote->getItemsCollection(array_keys($availableProductTypes), true) as $orderItem) {
                /* @var $orderItem Mage_Sales_Model_Order_Item */
                if (!$orderItem->getParentItem()) {
                    $qty = $orderItem->getQty();
                    $item = $this->initFromQuoteItem($orderItem, $qty);
                    if (is_string($item)) {
                        Mage::throwException($item);
                    }
                }
            }

//            $this->_initBillingAddressFromOrder($quote);
            //          $this->_initShippingAddressFromOrder($quote);

            //if (!$this->getQuote()->isVirtual() && $this->getShippingAddress()->getSameAsBilling()) {
            //print_r($this->getQuote()->getShippingAddress());
            //die();
            //echo 'ffff'.$this->getQuote()->getShippingAsBilling();
            //die();
            // echo $this->getQuote()->getR4qShippingAsBilling().'----';
            // die();
            if ($this->getQuote()->getR4qShippingAsBilling()) {
                $this->setShippingAsBilling(1);
            }

            //$this->setShippingMethod($order->getShippingMethod());
            //$this->getQuote()->getShippingAddress()->setShippingDescription($order->getShippingDescription());

            //$this->getQuote()->getPayment()->addData($order->getPayment()->getData());


            /*Mage::helper('core')->copyFieldset(
                'sales_copy_order',
                'to_edit',
                $quote,
                $this->getQuote()
            );

            Mage::dispatchEvent('request4quote_convert_quote_to_quote', array(
                'req' => $quote,
                'quote' => $this->getQuote()
            ));

            if (!$order->getCustomerId()) {
                $this->getQuote()->setCustomerIsGuest(true);
            }

            if ($this->getSession()->getUseOldShippingMethod(true)) {
                $this->collectShippingRates();
            } else {
                $this->collectRates();
            }

            // Make collect rates when user click "Get shipping methods and rates" in order creating
            // $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
            // $this->getQuote()->getShippingAddress()->collectShippingRates();
    */
            //$this->getQuote()->save();

            // $this->getQuote()->applyStocks();


            return $this;
        }

        protected function _initBillingAddressFromOrder(Mage_Sales_Model_Order $order)
        {
            $this->getQuote()->getBillingAddress()->setCustomerAddressId('');
            Mage::helper('core')->copyFieldset(
                'sales_copy_order_billing_address',
                'to_order',
                $order->getBillingAddress(),
                $this->getQuote()->getBillingAddress()
            );
        }

        protected function _initShippingAddressFromOrder(Mage_Sales_Model_Order $order)
        {
            $this->getQuote()->getShippingAddress()->setCustomerAddressId('');
            Mage::helper('core')->copyFieldset(
                'sales_copy_order_shipping_address',
                'to_order',
                $order->getShippingAddress(),
                $this->getQuote()->getShippingAddress()
            );
        }

        /**
         * Initialize creation data from existing order Item
         *
         * @param Mage_Sales_Model_Order_Item $orderItem
         * @param int $qty
         * @return Mage_Sales_Model_Quote_Item | string
         */
        public function initFromQuoteItem($orderItem, $qty = null)
        {
            if (!$orderItem->getId()) {
                return $this;
            }


            $product = Mage::getModel('catalog/product')
                ->setStoreId($this->getSession()->getStoreId())
                ->load($orderItem->getProductId());

            if ($product->getId()) {
                $product->setSkipCheckRequiredOption(true);
                $buyRequest = $orderItem->getBuyRequest();

                if (is_numeric($qty)) {
                    $buyRequest->setQty($qty);
                }
                $item = $this->getQuote()->addProduct($product, $buyRequest);
                $item->setQty($qty);

                // print_r($item->debug());
                //die();
                if (is_string($item)) {
                    return $item;
                }

                if ($additionalOptions = $orderItem->getProductOptionByCode('additional_options')) {
                    $item->addOption(new Varien_Object(
                        array(
                            'product' => $item->getProduct(),
                            'code' => 'additional_options',
                            'value' => serialize($additionalOptions)
                        )
                    ));
                }

                /*Mage::dispatchEvent('sales_convert_order_item_to_quote_item', array(
                    'order_item' => $orderItem,
                    'quote_item' => $item
                ));*/
                return $item;
            }

            return $this;
        }
    }