<?php
class ITwebexperts_Request4quote_Model_Observer {
	
	public function productLoadAfter($observer)
	{
		$product = $observer->getProduct();
		if (Mage::helper('request4quote')->isPriceHidden($product)) {

			$product->setCanShowPrice(false);

			if ($product->getTypeId() == 'bundle' || $product->getTypeId() == 'grouped') {
				$product->setR4qIsHiddenPrice(true);
				$product->setCanShowPrice(true);

			}

		}
	}
	
	public function productCollectionLoadAfter($observer)
	{
		$collection = $observer->getCollection();
		foreach ($collection AS $product) {
			if (Mage::helper('request4quote')->isPriceHidden($product)) {
				$product->setCanShowPrice(false);
			}
		}
	}

	public function beforeStockCheck($observer){
		$isAvailable = $observer->getEvent()->getIsavailable();
		$buyRequest = $observer->getEvent()->getBuyrequest();
			if(is_object($buyRequest)) {
				if ($buyRequest->getR4q()) {
					$isAvailable = true;
					$observer->getEvent()->setIsavailable($isAvailable);
				}
			}
			if(is_array($buyRequest)){
				if (isset($buyRequest['R4q'])){
					$isAvailable = true;
					$observer->getEvent()->setIsavailable($isAvailable);
				}
			}
	}
	
	public function getAttributeCodeForId($id, $attributeCode, $storeID = null)
	{
		if (is_null($storeID)) {
			if (Mage::app()->getStore()->isAdmin()) {
				$storeID = Mage::getSingleton('adminhtml/session_quote')->getStoreId();
			} else {
				$storeID = Mage::app()->getStore()->getId();
			}
		}
		return Mage::getResourceModel('catalog/product')->getAttributeRawValue($id, $attributeCode, $storeID);
	}

	public function priceBlockAfter($observer)
	{
		$block = $observer->getBlock();
		if ($block instanceof Mage_Catalog_Block_Product_Price || $block instanceof ITwebexperts_Payperrentals_Block_Catalog_Product_Viewprice) {
			$originalHTML = $observer->getTransport()->getHtml();
			$additionalHTML = '<input type="hidden" name="r4q_hidecart" value="'.(int)$this->getAttributeCodeForId($block->getProduct()->getId(), 'r4q_order_disabled').'" />';
			if ($this->getAttributeCodeForId($block->getProduct()->getId(),'r4q_enabled') && !Mage::registry('current_product')) {
				$additionalHTML .= '<input type="hidden" name="r4q_quote_enabled" value="'.(int)$block->getProduct()->getId().'" />';
			}
			$observer->getTransport()->setHtml( $originalHTML.$additionalHTML );
		}
		if($block instanceof ITwebexperts_Request4quote_Block_Checkout_Quote_Miniquote){
			$originalHTML = Mage::helper('core')->jsonEncode(preg_replace("!\r?\n!", "",$observer->getTransport()->getHtml()));
			$observer->getTransport()->setHtml( str_replace('\\\\\\','\\',$originalHTML));
			//$originalHTML = Mage::helper('core')->jsonEncode($observer->getTransport()->getHtml());
			//$observer->getTransport()->setHtml( $originalHTML);
		}
	}

    /**
     * First part sets status to ordered
     *
     * Second part is to import quote comments to order
     *
     * @param $observer
     * @return $this
     */
	
	public function orderPlaceAfter($observer)
	{
		$orders = $observer->getEvent()->getOrders();
        if (!$orders) {
            $orders = array($observer->getEvent()->getOrder());
        }

        if (!$orders) {
            return $this;
        }
		
		$quoteIds = array();
		foreach ($orders AS $order) {
			foreach ($order->getAllItems() AS $item) {
				$infoByRequest = $item->getBuyRequest();
				if ($infoByRequest->getData(ITwebexperts_Request4quote_Model_Quote::QUOTE_ID_OPTION)) {
					$quoteId = (int)$infoByRequest->getData(ITwebexperts_Request4quote_Model_Quote::QUOTE_ID_OPTION);
					$quoteIds[] = $quoteId;
				}
			}
		}
		if ($quoteIds) {
			$quoteIds = array_unique($quoteIds);
			foreach ($quoteIds AS $quoteId) {
				$quote = Mage::getModel('request4quote/quote')->loadByIdWithoutStore($quoteId);
				if ($quote->getId()) {
					$quote->setR4qStatus(ITwebexperts_Request4quote_Model_Quote::STATUS_ORDERED);
					$quote->save();
				}
			}
		}
	}

    public function importQuoteCommentsToOrder($observer){
        $order = $observer->getOrder();
        $items = $order->getAllItems();
            foreach ($items AS $item) {
                $infoByRequest = $item->getBuyRequest();
                if ($infoByRequest->getData(ITwebexperts_Request4quote_Model_Quote::QUOTE_ID_OPTION)) {
                    $fromR4q = true;
                    $quoteId = (int)$infoByRequest->getData(ITwebexperts_Request4quote_Model_Quote::QUOTE_ID_OPTION);
                }
            }
        if(isset($fromR4q) && $fromR4q && Mage::helper('request4quote')->enabledImportComments()){
            $quotecomments = Mage::getModel('request4quote/comments')->getCommentByR4qId($quoteId);
            foreach($quotecomments as $comment){
                $ordercomment = Mage::getModel('sales/order_status_history');
                $ordercomment->setParentId($order->getId());
                $ordercomment->setIsVisibleOnFront($comment->getIsVisibleOnFront());
                $ordercomment->setIsCustomerNotified($comment->getIsCustomerNotified());
                $ordercomment->setCreatedAt($comment->getCreatedAt());
                $ordercomment->setComment($comment->getSubmittedBy() . ': ' . $comment->getComment());
                $ordercomment->save();
            }
        }

    }

    public function afterRequest4quoteOrder($observer){
        $event = $observer->getEvent();
        $quote = $event->getQuote();
        $cart = $event->getCart();
        $optionsArr = array();
        foreach($quote->getAllVisibleItems() as $item){
            $quoteOptions = Mage::getModel('request4quote/quote_item_option')->getCollection()
                ->addFieldToFilter('item_id', $item->getId());
            foreach($quoteOptions as $quoteOption){
                $optionsArr[$quoteOption->getData('product_id')][$quoteOption->getData('code')] = $quoteOption->getData('value');
            }
        }
        if(count($optionsArr)){
            foreach($cart->getQuote()->getAllVisibleItems() as $item){
                foreach($optionsArr[$item->getData('product_id')] as $code=>$val){
                    if($code == 'info_buyRequest') continue;
                    $model = new Mage_Sales_Model_Quote_Item_Option();
                    $model->setData('item_id', $item->getId());
                    $model->setData('product_id', $item->getData('product_id'));
                    $model->setData('code', $code);
                    $model->setData('value', $val);
                    $model->save();
                }
            }
        }
    }

}