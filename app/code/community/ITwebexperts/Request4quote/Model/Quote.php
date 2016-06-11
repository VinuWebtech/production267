<?php

//maybe it should check if add or normal but not sure.
    class ITwebexperts_Request4quote_Model_Quote extends Mage_Sales_Model_Quote {

        const STATUS_NEW = 'new';
        const STATUS_PROCESSING = 'processing';
        const STATUS_PROCESSED = 'processed';
        const STATUS_ORDERED = 'ordered';
        const STATUS_DECLINED = 'declined';
        const STATUS_ACCEPTED = 'accepted';
        const STATUS_REJECTED = 'rejected';

        const PRICE_PROPOSAL_OPTION = 'r4q_price_proposal';
        const QUOTE_ID_OPTION = 'r4q_quote_id';

        protected $_eventPrefix = 'request4quote_quote';
        protected $_eventObject = 'r4q_quote';
        protected $__orderCurrency;

        protected function _construct()
        {
            $this->_init('request4quote/quote');
        }

        public function isR4q(){
            return true;
        }

        public function getQuoteUrl(){

        }

        public function assignCustomerWithAddressChange(
            Mage_Customer_Model_Customer    $customer,
            Mage_Sales_Model_Quote_Address  $billingAddress  = null,
            Mage_Sales_Model_Quote_Address  $shippingAddress = null
        )
        {
            if ($customer->getId()) {
                $this->setCustomer($customer);

                if (!is_null($billingAddress)) {
                    $this->setBillingAddress($billingAddress);
                } else {
                    $defaultBillingAddress = $customer->getDefaultBillingAddress();
                    if ($defaultBillingAddress && $defaultBillingAddress->getId()) {
                        $billingAddress = Mage::getModel('request4quote/quote_address')
                            ->importCustomerAddress($defaultBillingAddress);
                        $this->setBillingAddress($billingAddress);
                    }
                }

                if (is_null($shippingAddress)) {
                    $defaultShippingAddress = $customer->getDefaultShippingAddress();
                    if ($defaultShippingAddress && $defaultShippingAddress->getId()) {
                        $shippingAddress = Mage::getModel('request4quote/quote_address')
                            ->importCustomerAddress($defaultShippingAddress);
                    } else {
                        $shippingAddress = Mage::getModel('request4quote/quote_address');
                    }
                }
                $this->setShippingAddress($shippingAddress);
            }

            return $this;
        }


        public function getAddressesCollection()
        {
            if (is_null($this->_addresses)) {
                $this->_addresses = Mage::getModel('request4quote/quote_address')->getCollection()
                    ->setQuoteFilter($this->getId());

                if ($this->getId()) {
                    foreach ($this->_addresses as $address) {
                        $address->setQuote($this);
                    }
                }
            }
            return $this->_addresses;
        }

        protected function _getAddressByType($type)
        {
            foreach ($this->getAddressesCollection() as $address) {
                if ($address->getAddressType() == $type && !$address->isDeleted()) {
                    return $address;
                }
            }

            $address = Mage::getModel('request4quote/quote_address')->setAddressType($type);
            $this->addAddress($address);
            return $address;
        }

        /*public static function applyStocks($var){
           return parent::applyStocks($var);
        }*/


        public function getItemsCollection($useCache = true)
        {
            if ($this->hasItemsCollection()) {
                return $this->getData('items_collection');
            }
            if (is_null($this->_items)) {
                $this->_items = Mage::getModel('request4quote/quote_item')->getCollection();
                $this->_items->setQuote($this);
            }
            return $this->_items;
        }

        protected function _addCatalogProduct(Mage_Catalog_Model_Product $product, $qty = 1)
        {
            $newItem = false;
            $item = $this->getItemByProduct($product);
            if (!$item) {
                $item = Mage::getModel('request4quote/quote_item');
                $item->setQuote($this);
                if (Mage::app()->getStore()->isAdmin()) {
                    $item->setStoreId($this->getStore()->getId());
                }
                else {
                    $item->setStoreId(Mage::app()->getStore()->getId());
                }
                $newItem = true;
            }

            /**
             * We can't modify existing child items
             */
            if ($item->getId() && $product->getParentProductId()) {
                return $item;
            }

            $item->setOptions($product->getCustomOptions())
                ->setProduct($product);

            // Add only item that is not in quote already (there can be other new or already saved item
            if ($newItem) {
                $this->addItem($item);
            }

            return $item;
        }

        /*********************** PAYMENTS ***************************/
        public function getPaymentsCollection()
        {
            if (is_null($this->_payments)) {
                $this->_payments = Mage::getModel('request4quote/quote_payment')->getCollection()
                    ->setQuoteFilter($this->getId());

                if ($this->getId()) {
                    foreach ($this->_payments as $payment) {
                        $payment->setQuote($this);
                    }
                }
            }
            return $this->_payments;
        }

        /**
         * @return Mage_Sales_Model_Quote_Payment
         */
        public function getPayment()
        {
            foreach ($this->getPaymentsCollection() as $payment) {
                if (!$payment->isDeleted()) {
                    return $payment;
                }
            }
            $payment = Mage::getModel('request4quote/quote_payment');
            $this->addPayment($payment);
            return $payment;
        }

        public function formatPrice($price, $addBrackets = false)
        {
            return $this->formatPricePrecision($price, 2, $addBrackets);
        }


        public function formatPricePrecision($price, $precision, $addBrackets = false)
        {
            return $this->getOrderCurrency()->formatPrecision($price, $precision, array(), true, $addBrackets);
        }

        public function getOrderCurrency()
        {
            if (is_null($this->_orderCurrency)) {
                $this->_orderCurrency = Mage::getModel('directory/currency')->load($this->getQuoteCurrencyCode());
            }
            return $this->_orderCurrency;
        }

        /**
         * Retrieve status label
         *
         * @param   string $code
         * @return  string
         */
        public function getStatusLabel($code)
        {
            $status = Mage::getModel('request4quote/quote_status')
                ->load($code);
            return $status->getStoreLabel();
        }



    }
