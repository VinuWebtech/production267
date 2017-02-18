<?php

class ITwebexperts_Request4quote_QuoteController extends Mage_Core_Controller_Front_Action {
	
	/**
     * Retrieve r4q shopping cart model object
     *
     * @return ITwebexperts_Request4quote_Model_Cart
     */
	protected function _getCart()
    {
        return Mage::getSingleton('request4quote/cart');
    }
	
	
	/**
     * Get checkout session model instance
     *
     * @return ITwebexperts_Request4quote_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('request4quote/session');
    }
	
	/**
     * Get current active quote instance
     *
     * @return ITwebexperts_Request4quote_Model_Quote
     */
    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }
	
	
	/**
     * Set back redirect url to response
     *
     * @return ITwebexperts_Request4quote_Model_Cart
     */
    protected function _goBack()
    {
        $returnUrl = $this->getRequest()->getParam('return_url');
        if ($returnUrl) {

            if (!$this->_isUrlInternal($returnUrl)) {
                throw new Mage_Exception('External urls redirect to "' . $returnUrl . '" denied!');
            }

            $this->_getSession()->getMessages(true);
            $this->getResponse()->setRedirect($returnUrl);
        } elseif (!Mage::getStoreConfig('checkout/cart/redirect_to_cart')
            && !$this->getRequest()->getParam('in_cart')
            && $backUrl = $this->_getRefererUrl()
        ) {
            $this->getResponse()->setRedirect($backUrl);
        } else {
            if (($this->getRequest()->getActionName() == 'add') && !$this->getRequest()->getParam('in_cart')) {
                $this->_getSession()->setContinueShoppingUrl($this->_getRefererUrl());
            }
            $this->_redirect('request4quote_front/quote');
        }
        return $this;
    }
	
	/**
     * Initialize product instance from request data
     *
     * @return Mage_Catalog_Model_Product || false
     */
    protected function _initProduct()
    {
        $productId = (int) $this->getRequest()->getParam('product');
        if ($productId) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);
            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }

    /**
     * Add product to quote request
     *
     * @throws Mage_Exception
     *
     */
    
	public function addAction()
	{
		$cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();
        if($this->getRequest()->getParam('r4quote') && $this->getRequest()->getParam('r4quote') != 'new'){
            if(!Mage::registry('cquote') || Mage::registry('cquote')->getId() != $this->getRequest()->getParam('r4quote')){
                $cart->truncate();
                $quote = Mage::getModel('request4quote/quote')->loadByIdWithoutStore($this->getRequest()->getParam('r4quote'));
                $quoteArr = $quote->getItemsCollection();
                foreach($quoteArr as $quoteItem){
                    if(!$quoteItem->getParentItem()){
                        $optionCollection = Mage::getModel('request4quote/quote_item_option')->getCollection()
                                ->addItemFilter(array($quoteItem->getId()));
                        $optionArr = $optionCollection->getOptionsByItem($quoteItem);
                        foreach($optionArr as $option){
                            if($option->getCode()== 'info_buyRequest'){
                                $infoBuyRequest = unserialize($option->getValue());
                                $infoBuyRequest['r4q'] = 1;
                                $cart->addProduct($quoteItem->getProduct(), $infoBuyRequest);
                                break;
                            }
                        }
                    }
                }
                Mage::register('cquote', $quote);
            }
        }
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
			
			/**
			 * Check is quote request enabled
			 */
			if (!$product->getR4qEnabled()) {
				Mage::throwException(Mage::helper('request4quote')->__('Quote requests disabled for this product.'));
			}
			
			/**
			 * Related products
			 */
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                $this->_goBack();
                return;
            }

            $cart->addProduct($product, $params);
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $quote = $cart->getQuote();
            Mage::helper('request4quote')->saveStartEndDatesToQuote($quote,$product);

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent('r4q_checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            // Don't redirect if using ajax add to cart

            if(!Mage::getStoreConfig(ITwebexperts_Request4quote_Helper_Data::PATH_DISABLE_REDIRECT) || $this->getRequest()->getParam('from_listing') && !Mage::getStoreConfig(ITwebexperts_Request4quote_Helper_Data::PATH_DISABLE_REDIRECT_LISTING)){
                if (!$this->_getSession()->getNoCartRedirect(true)) {
                    if (!$cart->getQuote()->getHasError()){
                        $message = $this->__('%s was added to your Quote Request.', Mage::helper('core')->escapeHtml($product->getName()));
                        $this->_getSession()->addSuccess($message);
                    }
                    $this->_goBack();
                }
            }else{
                $message = $this->__('%s was added to your Quote Request.', Mage::helper('core')->escapeHtml($product->getName()));
                $this->getResponse()->setBody(Zend_Json::encode(array('status' => 1, 'message' => $message)));
            }
        } catch (Mage_Core_Exception $e) {
			$session = Mage::getSingleton('catalog/session');
            if ($session->getUseNotice(true)) {
                $session->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $session->addError(Mage::helper('core')->escapeHtml($message));
                }
            }
            // Don't redirect if using ajax add to cart
            if (!Mage::getStoreConfig(ITwebexperts_Request4quote_Helper_Data::PATH_DISABLE_REDIRECT) || $this->getRequest()->getParam('from_listing') && !Mage::getStoreConfig(ITwebexperts_Request4quote_Helper_Data::PATH_DISABLE_REDIRECT_LISTING)) {
                $url = $this->_getSession()->getRedirectUrl(true);
                if ($url) {
                    $this->getResponse()->setRedirect($url);
                } else {
                    $this->_redirectReferer(Mage::helper('request4quote/cart')->getCartUrl());
                }
            } else {
                $this->getResponse()->setBody(Zend_Json::encode(array('status' => 1)));
            }
        } catch (Exception $e) {
			$session = Mage::getSingleton('catalog/session');
            $session->addException($e, $this->__('Cannot add the item to Quote Request.'));
            Mage::logException($e);
            $this->_goBack();
        }
	}
	
	public function indexAction()
    {
        $cart = $this->_getCart();
        if ($cart->getQuote()->getItemsCount()) {
            $cart->init();
            $cart->save();
        }

        // Compose array of messages to add
        $messages = array();
        foreach ($cart->getQuote()->getMessages() as $message) {
            if ($message) {
                // Escape HTML entities in quote message to prevent XSS
                $message->setCode(Mage::helper('core')->escapeHtml($message->getCode()));
                $messages[] = $message;
            }
        }
        $cart->getCheckoutSession()->addUniqueMessages($messages);

        /**
         * if customer enteres shopping cart we should mark quote
         * as modified bc he can has checkout page in another window.
         */
        $this->_getSession()->setCartWasUpdated(true);

        Varien_Profiler::start(__METHOD__ . 'r4q_display');
        $this
            ->loadLayout()
            ->_initLayoutMessages('request4quote/session')
            ->_initLayoutMessages('catalog/session')
            ->getLayout()->getBlock('head')->setTitle($this->__('Request for Quote'));
        $this->renderLayout();
        Varien_Profiler::stop(__METHOD__ . 'r4q_display');
    }

    /**
     * When a customer submits a quote request or updates request
     *
     * @throws Mage_Exception
     */
	
	public function sendAction()
	{
		$quote = $this->_getQuote();
        $updateAction = (string)$this->getRequest()->getParam('update_cart_action');
        switch ($updateAction) {
            case 'empty_cart':
                $this->_emptyShoppingCart();
                $this->_goBack();
                break;
            case 'update_qty':
                $this->_updateShoppingCart();
                $this->_goBack();
                break;
            default:
                $this->_sendCart($quote);
        }
    }
    /**
     * Update customer's shopping cart
     */
    protected function _updateShoppingCart()
    {
        try {
            $cartData = $this->getRequest()->getParam('cart');
            if (is_array($cartData)) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                foreach ($cartData as $index => $data) {
                    if (isset($data['qty'])) {
                        $cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
                    }
                }
                $cart = $this->_getCart();
                if (! $cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
                    $cart->getQuote()->setCustomerId(null);
                }
                $cartData = $cart->suggestItemsQty($cartData);
                $cart->updateItems($cartData)
                    ->save();
            }
            $this->_getSession()->setCartWasUpdated(true);
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError(Mage::helper('core')->escapeHtml($e->getMessage()));
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot update shopping cart.'));
            Mage::logException($e);
        }
    }
    /**
     * Empty customer's shopping cart
     */
    protected function _emptyShoppingCart()
    {
        try {
            $this->_getCart()->truncate()->save();
            $this->_getSession()->setCartWasUpdated(true);
        } catch (Mage_Core_Exception $exception) {
            $this->_getSession()->addError($exception->getMessage());
        } catch (Exception $exception) {
            $this->_getSession()->addException($exception, $this->__('Cannot update shopping cart.'));
        }
    }

    /**
     * Minicart delete action
     */
    public function ajaxDeleteAction()
    {
        if (!$this->_validateFormKey()) {
            Mage::throwException('Invalid form key');
        }
        $id = (int) $this->getRequest()->getParam('id');
        $result = array();
        if ($id) {
            try {
                $this->_getCart()->removeItem($id)->save();

                $result['qty'] = $this->_getCart()->getSummaryQty();

                $this->loadLayout();
                $result['content'] = $this->getLayout()->getBlock('miniquote_content')->toHtml();

                $result['success'] = 1;
                $result['message'] = $this->__('Item was removed successfully.');
                Mage::dispatchEvent('ajax_cart_remove_item_success', array('id' => $id));
            } catch (Exception $e) {
                $result['success'] = 0;
                $result['error'] = $this->__('Can not remove the item.');
            }
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Minicart ajax update qty action
     */
    public function ajaxUpdateAction()
    {
        if (!$this->_validateFormKey()) {
            Mage::throwException('Invalid form key');
        }
        $id = (int)$this->getRequest()->getParam('id');
        $qty = $this->getRequest()->getParam('qty');
        $result = array();
        if ($id) {
            try {
                $cart = $this->_getCart();
                if (isset($qty)) {
                    $filter = new Zend_Filter_LocalizedToNormalized(
                        array('locale' => Mage::app()->getLocale()->getLocaleCode())
                    );
                    $qty = $filter->filter($qty);
                }

                $quoteItem = $cart->getQuote()->getItemById($id);
                if (!$quoteItem) {
                    Mage::throwException($this->__('Quote item is not found.'));
                }
                if ($qty == 0) {
                    $cart->removeItem($id);
                } else {
                    $quoteItem->setQty($qty)->save();
                }
                $this->_getCart()->save();

                $this->loadLayout();
                $result['content'] = $this->getLayout()->getBlock('miniquote_content')->toHtml();

                $result['qty'] = $this->_getCart()->getSummaryQty();

                if (!$quoteItem->getHasError()) {
                    $result['message'] = $this->__('Item was updated successfully.');
                } else {
                    $result['notice'] = $quoteItem->getMessage();
                }
                $result['success'] = 1;
            } catch (Exception $e) {
                $result['success'] = 0;
                $result['error'] = $this->__('Can not save item.');
            }
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Prepares the address object using data from $details and $data arrays.
     *
     * @param ITwebexperts_Request4quote_Model_Quote_Address $address
     * @param ITwebexperts_Request4quote_Model_Quote $quote
     * @param array $details
     * @param array $data
     */
    protected function _prepareAddress(ITwebexperts_Request4quote_Model_Quote_Address $address, ITwebexperts_Request4quote_Model_Quote $quote, $details, $data)
    {
        if (isset($details['email'])) {
            $quote->setData('customer_email', $details['email']);
            $address->setEmail($details['email']);
        }
        if (isset($details['firstname'])) {
            $quote->setData('customer_firstname', $details['firstname']);
            $address->setFirstname($details['firstname']);
        }
        if (isset($details['lastname'])) {
            $quote->setData('customer_lastname', $details['lastname']);
            $address->setLastname($details['lastname']);
        }
        if (isset($details['telephone'])) {
            $quote->setData('r4q_phone', $details['telephone']);
            $address->setTelephone($details['telephone']);
        }

        if (isset($data['country'])) {
            $address->setCountryId((string) $data['country']);
        } elseif (isset($data['country_id'])) {
            $address->setCountryId((string) $data['country_id']);
        }

        if (isset($data['postcode'])) {
            $address->setPostcode((string) $data['postcode']);
        }

        if (isset($data['city'])) {
            $address->setCity((string) $data['city']);
        }

        if (isset($data['company'])) {
            $address->setCompany((string) $data['company']);
        }

        if (isset($data['region_id'])) {
            $address->setRegionId((string) $data['region_id']);
        }

        if (isset($data['region'])) {
            $address->setRegion((string) $data['region']);
        }

        if (isset($data['address'])) {
            $address->setStreet((string) $data['address']." ".(string)$data['street']);
        } elseif (isset($data['street'])) {
            $address->setStreet((string) $data['street']);
        }

        $address->setCollectShippingRates(true);
    }

    /**
     * Processes billing/shipping addresses as well as the other parameters.
     * @param $quote
     */
    protected function _sendCart($quote){
		$r4qData = $this->getRequest()->getPost('r4q');
		if (isset($r4qData['details']) && is_array($r4qData['details'])) {
			
			$billingAddress = $quote->getBillingAddress();
            $shippingAddress = $quote->getShippingAddress();
			
			if (isset($r4qData['details']['remark']) && $r4qData['details']['remark'] != '') {
                $this->insertComment($r4qData['details']['remark'], $quote->getId());
			}

            if (isset($r4qData['billing']) && is_array($r4qData['billing'])) {

                $this->_prepareAddress($billingAddress, $quote, $r4qData['details'], $r4qData['billing']);

                $shippingSameAsBilling = false;
                if (isset($r4qData['billing']['shipping_same_as_billing'])) {
                    $shippingSameAsBilling = (bool)$r4qData['billing']['shipping_same_as_billing'];
                }

                if ($shippingSameAsBilling) {
                    $quote->setR4qShippingAsBilling(1);
                    $quote->getShippingAddress()->setShippingAsBilling(1);
                    $this->_prepareAddress($shippingAddress, $quote, $r4qData['details'], $r4qData['billing']);
                }
                elseif (isset($r4qData['shipping']) && is_array($r4qData['shipping'])) {
                    $this->_prepareAddress($shippingAddress, $quote, $r4qData['details'], $r4qData['shipping']);
                    $shippingAddress->setSameAsBilling(false);
                }
            }

            $quote->setData('r4q_status', ITwebexperts_Request4quote_Model_Quote::STATUS_NEW);
			$quote->setData('r4q_token', sha1(time() . rand(1000000000, 9999999999) . rand(1000000000, 9999999999)));
			$quote->setIsActive(false);

            /** Check if in guest quote mode if the email address used has a customer record, if so tie the
             * quote to that customer (so not an orphaned guest quote) */
            if(!$this->_getSession()->getCustomer()) {
                $email = $r4qData['details']['email'];
                $customer = Mage::getModel("customer/customer")->setWebsiteId(Mage::app()->getWebsite()->getId())->loadByEmail($email);
                if ($customer) {
                    $quote->setCustomerId($customer->getId())->setCustomerGroupId($customer->getGroupId());
                }
            }


			// save quote
			$quote->save();
		}
		
		if (isset($r4qData['item']) && is_array($r4qData['item'])) {
			foreach ($r4qData['item'] AS $itemId => $data) {
				$quoteItem = Mage::getModel('request4quote/quote_item')->load($itemId);
				if ($quoteItem->getQuoteId() == $quote->getId()) {
					if (isset($data['remark'])) {
						$quoteItem->setQuote($quote);
						$quoteItem->setData('r4q_note', $data['remark']);
						$quoteItem->save();
					}
				} else {
					// Exception
				}
			}
		}
		// cleanup session
		$this->_getSession()->clear();
		$this->_getSession()->setLastSuccessQuoteId($quote->getId());
		// success redirect
		$this->_redirect('request4quote_front/quote/success');
	}
	
	public function successAction()
	{
		if (!$this->_getSession()->getLastSuccessQuoteId()) {
			$this->_redirect('');
            return;
		}
		
		Mage::helper('request4quote/email')->sendRequestNotification($this->_getSession()->getLastSuccessQuoteId());
        Mage::helper('request4quote/email')->sendRequestNotificationAdmin($this->_getSession()->getLastSuccessQuoteId());
		
		$this->loadLayout()
            ->_initLayoutMessages('request4quote/session')
            ->_initLayoutMessages('catalog/session');
        $this->renderLayout();
		$this->_getSession()->clear();
	}
	
	public function viewAction()
	{
		$token = $this->getRequest()->getParam('token');
		$quoteCollection = Mage::getModel('request4quote/quote')->getCollection()
			->addFieldToFilter('r4q_token', $token)
			->load();
		$quote = $quoteCollection->getFirstItem();
		if (!$quote || !$quote->getId() || !$token) {
			$this->_getSession()->addError($this->__('Access denied.'));
			$this->_redirect('request4quote_front/customer_quote/history');
			return;
		} else {
            $storeid = $quote->getStore()->getId();
            Mage::app()->setCurrentStore($storeid);
			Mage::register('current_quote', $quote);
			$this->loadLayout()
				->_initLayoutMessages('request4quote/session')
				->_initLayoutMessages('customer/session');
			$this->renderLayout();
		}		
	}
	
	public function deleteAction()
	{
		$id = (int) $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $this->_getCart()->removeItem($id)
                  ->save();
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('Cannot remove the item.'));
                Mage::logException($e);
            }
        }
        $this->_redirectReferer(Mage::helper('request4quote/cart')->getCartUrl());
	}
	
	public function acceptAction()
	{
        $token = $this->getRequest()->getParam('token');
		$quoteId = (int) $this->getRequest()->getParam('quote_id');
		$checkoutSession = Mage::getSingleton('checkout/session');
		if ($quoteId) {
			$quote = Mage::getModel('request4quote/quote')->loadByIdWithoutStore($quoteId);
			if ($quote->getId()) {
                if($token != $quote->getR4qToken()){
                    $checkoutSession->addError($this->__('Wrong quote token.'));
                    $this->_redirect('request4quote_front/quote/view', array('quote_id' => $quote->getId()));
                }
				if ($quote->getR4qStatus() == ITwebexperts_Request4quote_Model_Quote::STATUS_PROCESSED) {
					$quote->setR4qStatus(ITwebexperts_Request4quote_Model_Quote::STATUS_ACCEPTED);
					$quote->save();
					Mage::helper('request4quote/email')->sendAcceptNotification($quote);
					$this->_getSession()->addSuccess('Quote has been accepted.');
					if ($quote->getCustomerId()) {
						$this->_redirect('request4quote_front/customer_quote/view', array('quote_id' => $quote->getId()));
					} else {
						$this->_redirect('request4quote_front/quote/view', array('token' => $quote->getR4qToken()));
					}
					
					return;
				} else {
					$checkoutSession->addError($this->__('Quote request is not processed.'));
				}
			} else {
				$checkoutSession->addError($this->__('Wrong quote id.'));
			}
		} else {
			$checkoutSession->addError($this->__('Wrong quote id.'));
		}
		$this->_redirect('checkout/cart/index');
	}
	
	public function rejectAction()
	{
		$quoteId = (int) $this->getRequest()->getParam('quote_id');
		$checkoutSession = Mage::getSingleton('checkout/session');
		if ($quoteId) {
			$quote = Mage::getModel('request4quote/quote')->loadByIdWithoutStore($quoteId);
			if ($quote->getId()) {
				if ($quote->getR4qStatus() == ITwebexperts_Request4quote_Model_Quote::STATUS_PROCESSED) {
					if ($reason = $this->getRequest()->getPost('r4q_reject_reason')) {
						$quote->setR4qRejectReason($reason);
					}
					$quote->setR4qStatus(ITwebexperts_Request4quote_Model_Quote::STATUS_REJECTED);
					$quote->save();
					Mage::helper('request4quote/email')->sendRejectNotification($quote);
					$this->_getSession()->addSuccess($this->__('Quote has been rejected.'));
					if ($quote->getCustomerId()) {
						$this->_redirect('request4quote_front/customer_quote/view', array('quote_id' => $quote->getId()));
					} else {
						$this->_redirect('request4quote_front/quote/view', array('token' => $quote->getR4qToken()));
					}
					return;
				} else {
					$checkoutSession->addError($this->__('Quote request is not processed.'));
				}
			} else {
				$checkoutSession->addError($this->__('Wrong quote id.'));
			}
		} else {
			$checkoutSession->addError($this->__('Wrong quote id.'));
		}
		$this->_redirect('checkout/cart/index');
	}

    /**
     * Add quote request to shoppping cart
     */
	
	public function orderAction()
	{
		$quoteId = (int) $this->getRequest()->getParam('quote_id');
		$checkoutSession = Mage::getSingleton('checkout/session');
		if ($quoteId) {
			$quote = Mage::getModel('request4quote/quote')->loadByIdWithoutStore($quoteId);

			if ($quote->getId()) {
					$cart = Mage::getSingleton('checkout/cart');
                    if($quote->getStoreId()){
                        $quotestore = $quote->getStoreId();
                    } else {
                        $quotestore = Mage::app()->getStore()->getId();
                    }
					foreach ($quote->getAllItems() AS $item) {
						
						if ($item->getParentItem()) continue;
						
						$product = $item->getProduct();
						
						if ($product) {
                            $product = Mage::getModel('catalog/product')
                                ->setStoreId($quotestore)
                                ->load($product->getId());
							$info = $item->getOptionByCode('info_buyRequest');
							if ($info) {
								$infoBuyReqest = new Varien_Object(unserialize($info->getValue()));
							} else {
								$infoBuyReqest = new Varien_Object(array('qty' => 1));
							}
							$infoBuyReqest->setQty($item->getQty());
							$infoBuyReqest->setData(
								ITwebexperts_Request4quote_Model_Quote::PRICE_PROPOSAL_OPTION,
								$item->getR4qPriceProposal()
							);
							$infoBuyReqest->setData(
								ITwebexperts_Request4quote_Model_Quote::QUOTE_ID_OPTION,
								$quote->getId()
							);

							try {
								$cart->addProduct($product, $infoBuyReqest);
                                if($quote->getCouponCode()) {
                                    $cart->getQuote()->setCouponCode($quote->getCouponCode());
                                }
							} catch (Exception $e) {
								$checkoutSession->addError($this->__($e->getMessage()));
							}
							
						} else {
							$checkoutSession->addError($this->__('Product does not exists anymore.'));
						}
					}
                $cart->save();
                Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
                Mage::dispatchEvent('after_request4quote_order', array('quote' => $quote, 'cart' => $cart));

			} else {
				$checkoutSession->addError($this->__('Wrong quote id.'));
			}
		} else {
			$checkoutSession->addError($this->__('Wrong quote id.'));
		}
		$this->_redirect('checkout/cart/index');
	}

    /**
     * Initialize shipping information
     */
    public function estimatePostAction()
    {
        $quote = $this->_getQuote();
        $address = $quote->getShippingAddress();

        $data = array(
            'country_id' => (string) $this->getRequest()->getParam('country_id'),
            'postcode' => (string) $this->getRequest()->getParam('estimate_postcode'),
            'city' => (string) $this->getRequest()->getParam('estimate_city'),
            'region_id' => (string) $this->getRequest()->getParam('region_id'),
            'region' => (string) $this->getRequest()->getParam('region'),
        );

        $this->_prepareAddress($address, $quote, array(), $data);
        $quote->save();
        $this->_goBack();
    }

    /**
     * Estimate update action
     *
     * @return null
     */
    public function estimateUpdatePostAction()
    {
        $code = (string) $this->getRequest()->getParam('estimate_method');
        if (!empty($code)) {
            $this->_getQuote()->getShippingAddress()->setShippingMethod($code)/*->collectTotals()*/->save();
        }
        $this->_goBack();
    }

    public function historyAction() {
        $this->_redirect('request4quote_front/customer_quote/history');
        return;
    }

    public function insertComment($comment, $quoteid){
        $customerTranlate = Mage::helper('request4quote')->__('customer');
        $commentItem = Mage::getModel('request4quote/comments');
        $commentItem->setComment($comment);
        $commentItem->setR4qId($quoteid);
        $commentItem->setStatus($this->_getQuote()->getR4qStatus());
        $commentItem->setIsCustomerNotified(1);
        $commentItem->setIsVisibleOnFront(1);
        $commentItem->setCreatedAt(time());
        $commentItem->setSubmittedBy($customerTranlate);
        try {
            $commentItem->save();
        } catch (Exception $e){
            Mage::log($e);
        }
    }

    public function submitCommentAction(){
        $comment = $this->getRequest()->getParam('comment');
        $quoteid = $this->getRequest()->getParam('quoteid');
        if(!$this->_validateFormKey()){
            $this->_getSession()->addError($this->__('Invalid form key'));
            $this->_redirect('request4quote_front/customer_quote/view', array('quote_id' => $quoteid));
        }
        if($comment == '' || $comment == null){
            $this->_getSession()->addError($this->__('Comment can not be blank'));
            $this->_redirect('request4quote_front/customer_quote/view', array('quote_id' => $quoteid));
        }
        $this->insertComment($comment, $quoteid);
        Mage::helper('request4quote/email')->sendCommentUpdateToadmin($quoteid, $comment);
        $this->_getSession()->addSuccess($this->__('Quote comment successfully added'));
        $this->_redirectReferer();
        //$this->_redirect('request4quote_front/customer_quote/view', array('quote_id' => $quoteid));
    }
}

