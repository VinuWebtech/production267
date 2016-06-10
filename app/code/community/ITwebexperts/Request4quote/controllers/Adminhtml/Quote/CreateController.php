<?php
require_once('Mage/Adminhtml/controllers/Sales/Order/CreateController.php');
class ITwebexperts_Request4quote_Adminhtml_Quote_CreateController extends Mage_Adminhtml_Sales_Order_CreateController {

    protected function _isAllowed(){
        return Mage::getSingleton('admin/session')->isAllowed('admin/request4quote/quotes');
    }

    protected function _getSession()
    {
        return Mage::getSingleton('request4quote/adminhtml_session_quote');
    }
    
    
    protected function _initSession()
    {
        /**
         * Identify customer
         */
        if ($customerId = $this->getRequest()->getParam('customer_id')) {
            $this->_getSession()->setCustomerId((int) $customerId);
        }

        /**
         * Identify store
         */
        if ($storeId = $this->getRequest()->getParam('store_id')) {
            $this->_getSession()->setStoreId((int) $storeId);
        }

        /**
         * Identify currency
         */
        if ($currencyId = $this->getRequest()->getParam('currency_id')) {
            $this->_getSession()->setCurrencyId((string) $currencyId);
            $this->_getOrderCreateModel()->setRecollect(true);
        }
        return $this;
    }
    
    protected function _getQuote()
    {
        return $this->_getSession()->getQuote();
    }
    
    protected function _getOrderCreateModel()
    {
        return Mage::getSingleton('request4quote/adminhtml_quote_create');
    }
    
    
    public function indexAction()
    {
        $this->_title($this->__('Request4Quote'))->_title($this->__('Quotes'))->_title($this->__('New Request'));
        $this->_initSession();
        $this->loadLayout();

        $this->_setActiveMenu('request4quote/quote')
            ->renderLayout();
    }
    
    protected function _reloadQuote()
    {
        $id = $this->_getQuote()->getId();
        $this->_getQuote()->load($id);
        return $this;
    }
    
    public function loadBlockAction()
    {
        $request = $this->getRequest();
        try {
            $this->_initSession()
                ->_processData();
        }
        catch (Mage_Core_Exception $e){
            $this->_reloadQuote();
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e){
            $this->_reloadQuote();
            $this->_getSession()->addException($e, $e->getMessage());
        }


        $asJson= $request->getParam('json');
        $block = $request->getParam('block');

        $update = $this->getLayout()->getUpdate();
        if ($asJson) {
            $update->addHandle('adminhtml_quote_create_load_block_json');
        } else {
            $update->addHandle('adminhtml_quote_create_load_block_plain');
        }

        if ($block) {
            $blocks = explode(',', $block);
            if ($asJson && !in_array('message', $blocks)) {
                $blocks[] = 'message';
            }

            foreach ($blocks as $block) {
                $update->addHandle('adminhtml_quote_create_load_block_' . $block);
            }
        }
        $this->loadLayoutUpdates()->generateLayoutXml()->generateLayoutBlocks();
        $result = $this->getLayout()->getBlock('content')->toHtml();
        if ($request->getParam('as_js_varname')) {
            Mage::getSingleton('adminhtml/session')->setUpdateResult($result);
            $this->_redirect('*/*/showUpdateResult');
        } else {
            $this->getResponse()->setBody($result);
        }
    }
    
    public function addConfiguredAction()
    {
        $errorMessage = null;
        try {
            $this->_initSession()
                ->_processData();
        }
        catch (Exception $e){
            $this->_reloadQuote();
            $errorMessage = $e->getMessage();
        }

        // Form result for client javascript
        $updateResult = new Varien_Object();
        if ($errorMessage) {
            $updateResult->setError(true);
            $updateResult->setMessage($errorMessage);
        } else {
            $updateResult->setOk(true);
        }

        $updateResult->setJsVarName($this->getRequest()->getParam('as_js_varname'));
        Mage::getSingleton('adminhtml/session')->setCompositeProductResult($updateResult);
        $this->_redirect('*/catalog_product/showUpdateResult');
    }
    
    public function startAction()
    {
        $this->_getSession()->clear();
        $this->_redirect('*/*', array('customer_id' => $this->getRequest()->getParam('customer_id')));
    }
    
    public function cancelAction()
    {
        $this->_getSession()->clear();
        $this->_redirect('*/*');
    }
    
    public function saveAction()
    {
        try {
            $quote = $this->_getQuote();
            if (count($this->_getQuote()->getAllItems())) {
                $data = $this->getRequest()->getPost('order');
                $comment = $this->getRequest()->getPost('comment');
                $commentForm = isset($comment['comment']) ? $comment['comment'] : '';
                $accountData = $data['account'];
                $status = $this->getRequest()->getPost('r4q_status');
                // $status = Mage::getModel('request4quote/quote_status')->load($status, 'label');
                $appendComments = isset($data['comment']['customer_note_notify']) ? $data['comment']['customer_note_notify'] : '';
                $visibleFront = isset($comment['is_visible_on_front']) ? $comment['is_visible_on_front'] : '';
                $notifyCustomer = isset($comment['is_customer_notified']) ? $comment['is_customer_notified'] : '';
                $salesrep = $this->getRequest()->getPost('salesrep');

                if (isset($data['billing_address'])){
                    $accountData = array_merge($data['account'], $data['billing_address']);
                }
                $this->_getOrderCreateModel()->setData('account', $accountData);
                $this->_getOrderCreateModel()->saveCustomer();
                $quote->setCustomerFirstname($accountData['firstname']);
                $quote->setCustomerLastname($accountData['lastname']);
                $quote->setCustomerEmail($accountData['email']);
                $quote->save();

                if (!$this->_getQuote()->getR4qToken()) {
                    $this->_getQuote()->setR4qToken(sha1(time() . rand(1000000000, 9999999999) . rand(1000000000, 9999999999)));
                }

                if(!$this->getRequest()->getPost('shipping_same_as_billing' || Mage::helper('request4quote')->canShowBillingAddressAdmin() == 0)){
                    $syncFlag = 0;
                }else{
                    $syncFlag = $this->getRequest()->getPost('shipping_same_as_billing') == 'on'?1:0;
                }

                $quote->setR4qShippingAsBilling((int)$syncFlag);

                //save item data
                $r4qData = $this->getRequest()->getPost('item');
                foreach ($r4qData as $itemId => $itemData) {
                        $r4qItem = Mage::getModel('request4quote/quote_item')->load($itemId);
                    $r4Quote = Mage::getModel('request4quote/quote')->loadByIdWithoutStore($quote->getId());
                    if ($r4qItem->getId() && $r4Quote->getId()) {
                        $r4qItem->setQuote($r4Quote);
                        if (isset($itemData['r4q_note'])) {
                            $r4qItem->setR4qNote($itemData['r4q_note']);
                        }
                        if (isset($itemData['r4q_price_proposal']) && is_numeric($itemData['r4q_price_proposal'])) {
                            $priceProposal = (float)$itemData['r4q_price_proposal'];
                            $r4qItem->setR4qPriceProposal($priceProposal);
                        }
                        $r4qItem->save();
                    } else {
                        throw new Exception($this->__('Wrong Quote Item ID'));
                    }
                }
                foreach($this->_getQuote()->getAllItems() as $item){
                    Mage::helper('request4quote')->saveStartEndDatesToQuote($quote, $item);
                }

                $quote->setSalesrep($salesrep);
                $quote->setR4qPhone($accountData['telephone']);
                $quote->setR4qStatus($status);


                /** Save quote comments */
                if($appendComments){
                    $commentItem = Mage::getModel('request4quote/comments');
                    $commentItem->setComment($commentForm);
                    $commentItem->setR4qId($quote->getId());
                    $commentItem->setStatus($status);
                    $commentItem->setIsCustomerNotified($notifyCustomer);
                    $commentItem->setIsVisibleOnFront($visibleFront);
                    $commentItem->setCreatedAt(time());
                    $user = Mage::getSingleton('admin/session');
                    $adminname = $user->getUser()->getFirstname() . ' ' . $user->getUser()->getLastname();
                    $commentItem->setSubmittedBy('admin: ' . $adminname);
                    try {
                        $commentItem->save();
                    } catch (Exception $e){
                        Mage::log($e);
                    }
                }
                $quote->save();


                /** Send Email */
            if ($data['send_confirmation'] == 1) {
                $emailComments = null;
                if($appendComments && $notifyCustomer){
                    $emailComments = $commentForm;
                }
                Mage::helper('request4quote/email')->sendRequestProposalNotification($quote, $emailComments);
            }
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Quote request has been saved successfully.'));
                $this->_redirect('*/quote/');
            } else {
                throw new Exception($this->__('Please add at least one product to the request.'));
            }
        } catch (Mage_Payment_Model_Info_Exception $e) {
            $this->_getOrderCreateModel()->saveQuote();
            $message = $e->getMessage();
            if( !empty($message) ) {
                $this->_getSession()->addError($message);
            }
            $this->_redirect('*/*/');
        } catch (Mage_Core_Exception $e){
            $message = $e->getMessage();
            if( !empty($message) ) {
                $this->_getSession()->addError($message);
            }
            $this->_redirect('*/*/');
        }
        catch (Exception $e){
            $this->_getSession()->addException($e, $this->__('Quote request saving error: %s', $e->getMessage()));
            $this->_redirect('*/*/');
        }
    }
    
    public function configureProductToAddAction()
    {
        // Prepare data
        $productId  = (int) $this->getRequest()->getParam('id');

        $configureResult = new Varien_Object();
        $configureResult->setOk(true);
        $configureResult->setProductId($productId);
        $sessionQuote = Mage::getSingleton('request4quote/adminhtml_session_quote');
        $configureResult->setCurrentStoreId($sessionQuote->getStore()->getId());
        $configureResult->setCurrentCustomerId($sessionQuote->getCustomerId());

        // Render page
        /* @var $helper Mage_Adminhtml_Helper_Catalog_Product_Composite */
        $helper = Mage::helper('adminhtml/catalog_product_composite');
        $helper->renderConfigureResult($this, $configureResult);

        return $this;
    }

    public function configureQuoteItemsAction()
    {
        // Prepare data
        $configureResult = new Varien_Object();
        try {
            $quoteItemId = (int) $this->getRequest()->getParam('id');
            if (!$quoteItemId) {
                Mage::throwException($this->__('Quote item id is not received.'));
            }

            $quoteItem = Mage::getModel('request4quote/quote_item')->load($quoteItemId);
            if (!$quoteItem->getId()) {
                Mage::throwException($this->__('Quote item is not loaded.'));
            }

            $configureResult->setOk(true);
            $optionCollection = Mage::getModel('request4quote/quote_item_option')->getCollection()
                    ->addItemFilter(array($quoteItemId));
            $quoteItem->setOptions($optionCollection->getOptionsByItem($quoteItem));

            $configureResult->setBuyRequest($quoteItem->getBuyRequest());

            $sessionQuote = Mage::getSingleton('request4quote/adminhtml_session_quote');
            $configureResult->setCurrentStoreId($sessionQuote->getStore()->getId());
            $configureResult->setProductId($quoteItem->getProductId());
            $configureResult->setCurrentCustomerId($sessionQuote->getCustomerId());

        } catch (Exception $e) {
            $configureResult->setError(true);
            $configureResult->setMessage($e->getMessage());
        }

        // Render page
        /* @var $helper Mage_Adminhtml_Helper_Catalog_Product_Composite */
        $helper = Mage::helper('adminhtml/catalog_product_composite');
        $helper->renderConfigureResult($this, $configureResult);

        return $this;
    }

    public function processDataAction()
    {
        $this->_initSession();
        $this->_processData();
        $this->_forward('index');
    }
    
    protected function _processData()
    {
        return $this->_processActionData();
    }
    
    
    protected function _processActionData($action = null)
    {
        $eventData = array(
            'order_create_model' => $this->_getOrderCreateModel(),
            'request_model'      => $this->getRequest(),
            'session'            => $this->_getSession(),
        );

        Mage::dispatchEvent('r4q_adminhtml_quote_create_process_data_before', $eventData);

        /**
         * Saving order data
         */
        if ($data = $this->getRequest()->getPost('order')) {
            $this->_getOrderCreateModel()->importPostData($data);
        }

        /**
         * Initialize catalog rule data
         */
        $this->_getOrderCreateModel()->initRuleData();

        /**
         * init first billing address, need for virtual products
         */
        $this->_getOrderCreateModel()->getBillingAddress();

        if (!$this->_getOrderCreateModel()->getQuote()->isVirtual()) {
            $this->_getOrderCreateModel()->setShippingAsBilling(0);
        }
        $shippingMethod = $this->_getOrderCreateModel()->getShippingAddress()->getShippingMethod();
        /**
         * Change shipping address flag
         */
       // if (!$this->_getOrderCreateModel()->getQuote()->isVirtual() && $this->getRequest()->getPost('reset_shipping')) {
            $syncFlag = $this->getRequest()->getPost('shipping_as_billing');
                $this->_getOrderCreateModel()->setShippingAsBilling((int)$syncFlag);
            $this->_getQuote()->setR4qShippingAsBilling((int)$syncFlag);
       // }



        /**
         * Apply mass changes from sidebar
         */
        if ($data = $this->getRequest()->getPost('sidebar')) {
            $this->_getOrderCreateModel()->applySidebarData($data);
        }

        /**
         * Adding product to quote from shopping cart, wishlist etc.
         */
        if ($productId = (int) $this->getRequest()->getPost('add_product')) {
            $this->_getOrderCreateModel()->addProduct($productId, $this->getRequest()->getPost());
        }

        /**
         * Adding products to quote from special grid
         */
        if ($this->getRequest()->has('item') && !$this->getRequest()->getPost('update_items') && !($action == 'save')) {
            $items = $this->getRequest()->getPost('item');
            $items = $this->_processFiles($items);
            $this->_getOrderCreateModel()->addProducts($items);
        }

        /**
         * Update quote items
         */
        if ($this->getRequest()->getPost('update_items')) {
            $items = $this->getRequest()->getPost('item', array());
            $items = $this->_processFiles($items);
            $this->_getOrderCreateModel()->updateQuoteItems($items);
        }

        /**
         * Remove quote item
         */
        $removeItemId = (int) $this->getRequest()->getPost('remove_item');
        $removeFrom = (string) $this->getRequest()->getPost('from');
        if ($removeItemId && $removeFrom) {
            $this->_getOrderCreateModel()->removeItem($removeItemId, $removeFrom);
        }

        /**
         * Collecting shipping rates
         */
        if (!$this->_getOrderCreateModel()->getQuote()->isVirtual() //&&
            //    $this->getRequest()->getPost('collect_shipping_rates')
        ) {
            $this->_getOrderCreateModel()->collectShippingRates();
        }

        /**
         * Move quote item
         */
        $moveItemId = (int) $this->getRequest()->getPost('move_item');
        $moveTo = (string) $this->getRequest()->getPost('to');
        if ($moveItemId && $moveTo) {
            $this->_getOrderCreateModel()->moveQuoteItem($moveItemId, $moveTo, 1);
        }


        $eventData = array(
            'order_create_model' => $this->_getOrderCreateModel(),
            'request'            => $this->getRequest()->getPost(),
        );

        Mage::dispatchEvent('r4q_adminhtml_quote_create_process_data', $eventData);

        $this->_getOrderCreateModel()
            ->saveQuote();




        return $this;
    }
    
    public function showUpdateResultAction()
    {
        $session = Mage::getSingleton('adminhtml/session');
        if ($session->hasUpdateResult() && is_scalar($session->getUpdateResult())){
            $this->getResponse()->setBody($session->getUpdateResult());
            $session->unsUpdateResult();
        } else {
            $session->unsUpdateResult();
            return false;
        }
    }
    
    protected function _processFiles($items)
    {
        /* @var $productHelper Mage_Catalog_Helper_Product */
        $productHelper = Mage::helper('catalog/product');
        foreach ($items as $id => $item) {
            $buyRequest = new Varien_Object($item);
            $params = array('files_prefix' => 'item_' . $id . '_');
            $buyRequest = $productHelper->addParamsToBuyRequest($buyRequest, $params);
            if ($buyRequest->hasData()) {
                $items[$id] = $buyRequest->toArray();
            }
        }
        return $items;
    }



    
}