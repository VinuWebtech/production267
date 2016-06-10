<?php

class ITwebexperts_Request4quote_Adminhtml_QuoteController extends Mage_Adminhtml_Controller_Action
{
	/**
	 * Array of actions which can be processed without secret key validation
	 *
	 * @var array
	 */
	protected $_publicActions = array('redirecttoquote');

	public function redirecttoquoteAction()
	{

		Mage::app()->getResponse()->setRedirect(Mage::getUrl('adminhtml/quote_edit/start', array('quote_id' => Mage::app()->getRequest()->getParam('quote_id'), Mage_Adminhtml_Model_Url::SECRET_KEY_PARAM_NAME => Mage::getSingleton('adminhtml/url')->getSecretKey('adminhtml_quote_edit', 'start'))));
	}

	protected function _isAllowed(){
        return Mage::getSingleton('admin/session')->isAllowed('admin/request4quote/quotes');
    }

	protected function _initQuote($idFieldName = 'quote_id')
    {
        $this->_title($this->__('Request4Quote'))->_title($this->__('Manage Quote Requests'));

        $quoteId = (int) $this->getRequest()->getParam($idFieldName);
        $quote = Mage::getModel('request4quote/quote');
        if ($quoteId) {
            $quote->loadByIdWithoutStore($quoteId);

        }
        Mage::register('current_quote', $quote);
        return $this;
    }

	public function indexAction()
	{
		$this->_title($this->__('Request4Quote'))->_title($this->__('Manage Quote Requests'));
		$this->loadLayout();
        $this->renderLayout();
	}

	public function editAction()
	{
        $this->_initQuote('quote_id');
		$this->loadLayout();
        $this->renderLayout();
	}

	public function massChangeStatusAction()
	{
		{
			$r4qIds = $this->getRequest()->getParam('request4quote');
			$status = $this->getRequest()->getParam('status');
			if (!is_array($r4qIds)) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
			} else {
				try {
					foreach ($r4qIds as $r4qId) {
						$r4q = Mage::getModel('request4quote/quote')->loadByIdWithoutStore($r4qId);
						$r4q->setR4qStatus($status);
						$r4q->save();
					}
					Mage::getSingleton('adminhtml/session')->addSuccess(
						Mage::helper('adminhtml')->__(
							'Total of %d record(s) were successfully updated to status: %s', count($r4qIds), $status
						)
					);
				} catch (Exception $e) {
					Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				}
			}
			$this->_redirect('*/*/index');
		}
	}


	public function massDeleteAction()
	{
		$r4qIds = $this->getRequest()->getParam('request4quote');
		if (!is_array($r4qIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
		} else {
			try {
				foreach ($r4qIds as $r4qId) {
					$r4q = Mage::getModel('request4quote/quote')->loadByIdWithoutStore($r4qId);
					$r4q->delete();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(
					Mage::helper('adminhtml')->__(
						'Total of %d record(s) were successfully deleted', count($r4qIds)
					)
				);
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/index');
	}

	/**
	 * Convert quote to order
	 *
	 * @return $this
	 */

	public function orderAction()
	{
		$quoteId = $this->getRequest()->getParam('quote_id');
		try {
			$this->_initQuote('quote_id');
			$r4qQuote = Mage::registry('current_quote');
			if (!$r4qQuote->getId()) {
				throw new Exception($this->__('Wrong Quote ID'));
			}
			$orderSession = Mage::getSingleton('adminhtml/session_quote');
			$orderSession->clear();
			$orderCreateModel = Mage::getSingleton('adminhtml/sales_order_create');

			$importData = array();
			$customerModel = Mage::getModel('customer/customer');
			$websites = Mage::app()->getWebsites();
			foreach($websites as $website) {
				$customerModel->setWebsiteId($website->getId());
				$customerModel->loadByEmail($r4qQuote->getCustomerEmail());
				if ($customerModel->getId()) break;
			}

			//here needs to check if on import carries the entity_id. Thats why it might needs so the billing address has the same data as the rfq address
			$billingAddressArray = $r4qQuote->getBillingAddress()->getData();
			unset($billingAddressArray['address_id']);
			$shippingAddressArray = $r4qQuote->getShippingAddress()->getData();
			unset($shippingAddressArray['address_id']);

			if ($customerModel->getId()) {
				$orderSession->setCustomerId($customerModel->getId());
				$importData['order'] = array(
					'account' => array(
						'email' => $r4qQuote->getCustomerEmail()
					),
					'billing_address' => $billingAddressArray
				);
			} else {
				$orderSession->setCustomerId(0);
				if ($r4qQuote->getCustomerEmail()) {
					$importData['order'] = array(
						'account' => array(
							'email' => $r4qQuote->getCustomerEmail()
						),
						'billing_address' => $billingAddressArray
					);
					$importData['account'] = array(
							'email' => $r4qQuote->getCustomerEmail()
					);
				}
			}
			if ($r4qQuote->getQuoteCurrencyCode()) {
				$orderSession->setCurrencyId($r4qQuote->getQuoteCurrencyCode());
			}
			$orderSession->setStoreId($r4qQuote->getStoreId());
			if($r4qQuote->getShippingAddress()->getShippingMethod()){
				$orderSession->setShippingMethod($r4qQuote->getShippingAddress()->getShippingMethod());
			}
			// import order data
			$orderCreateModel->importPostData($importData);
            if ($r4qQuote->getBillingAddress()) {
                $orderCreateModel->setBillingAddress($billingAddressArray);
            }

            if($r4qQuote->getShippingAddress()){
                $orderCreateModel->setShippingAddress($shippingAddressArray);
            }
            if (!$r4qQuote->getR4qShippingAsBilling() || $r4qQuote->getR4qShippingAsBilling() == '0') {
                   //$orderCreateModel->setShippingAddress($r4qQuote->getBillingAddress());
                   $orderCreateModel->setShippingAsBilling(0);
            }
			// add items

			foreach ($r4qQuote->getAllItems() AS $item) {
				if ($item->getParentItem()) continue;
				$product = $item->getProduct();
				if ($product) {
					$product = Mage::getModel('catalog/product')
						->setStoreId($r4qQuote->getStoreId())
						->load($product->getId());
					$info = $item->getOptionByCode('info_buyRequest');
					if ($info) {
						$infoBuyRequest = new Varien_Object(unserialize($info->getValue()));
					} else {
						$infoBuyRequest = new Varien_Object(array('qty' => 1));
					}
					if(isset($infoBuyRequest['start_date'])){
						$infoBuyRequest['start_date'] = date('Y-m-d', strtotime($infoBuyRequest['start_date']));
					}
					if(isset($infoBuyRequest['end_date'])){
						$infoBuyRequest['end_date'] = date('Y-m-d', strtotime($infoBuyRequest['end_date']));
					}
                    if (Mage::helper('request4quote')->isOpenendedInstalled()) {
                        $infoBuyRequest[ITwebexperts_Openendedinvoices_Helper_Data::BILLING_PERIOD_CUSTOM_PRICE] = $item->getR4qPriceProposal();
                    }
					$infoBuyRequest->setQty($item->getQty());
                    $infoBuyRequest->setStockId($item->getStockId());
                    if($infoBuyRequest->getSelPay() && $infoBuyRequest->getSelPay() == 'recurring'){
					$infoBuyRequest->setData(
						ITwebexperts_Request4quote_Model_Quote::PRICE_PROPOSAL_OPTION,
                            0
                        );
                    }
                    else{
                        $infoBuyRequest->setData(
                            ITwebexperts_Request4quote_Model_Quote::PRICE_PROPOSAL_OPTION,
						$item->getR4qPriceProposal()
					);
                    }
					$infoBuyRequest->setData(
						ITwebexperts_Request4quote_Model_Quote::QUOTE_ID_OPTION,
						$r4qQuote->getId()
					);
                    $orderCreateModel->addProduct($product,$infoBuyRequest->getData());
				}
			}
			if($r4qQuote->getCouponCode()){
				$orderCreateModel->applyCoupon($r4qQuote->getCouponCode());
			}
			$orderCreateModel->saveQuote();
			$this->_redirect('adminhtml/sales_order_create/index');
			return $this;
		} catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            Mage::logException($e);
        }
		$this->_redirect('*/*/');
	}

	/**
	 * Add order comment action via ajax
	 */
	public function addCommentAction()
	{
		$quoteid = $this->getRequest()->getParam('quote_id');
            try {
				$response = false;
				$data = $this->getRequest()->getPost('comment');
				$notify = isset($data['is_customer_notified']) ? $data['is_customer_notified'] : false;
				$visible = isset($data['is_visible_on_front']) ? $data['is_visible_on_front'] : false;
				$comment = trim(strip_tags($data['comment']));
				$status = $this->getRequest()->getPost('r4q_status');

				$quote = Mage::getModel('request4quote/quote');
				if ($quoteid) {
					$quote->loadByIdWithoutStore($quoteid);
					$quote->setR4qStatus($status);
					$quote->save();
				}

				$commentItem = Mage::getModel('request4quote/comments');
                $adminTranslate = Mage::helper('request4quote')->__('admin');
				$commentItem->setComment($comment);
				$commentItem->setR4qId($quoteid);
				$commentItem->setStatus($status);
				$commentItem->setIsCustomerNotified($notify);
				$commentItem->setIsVisibleOnFront($visible);
				$commentItem->setCreatedAt(time());
				$user = Mage::getSingleton('admin/session');
				$adminname = $user->getUser()->getFirstname() . ' ' . $user->getUser()->getLastname();
				$commentItem->setSubmittedBy($adminTranslate . ': ' . $adminname);

				if($notify){
					Mage::helper('request4quote/email')->sendCommentUpdate($quoteid, $comment);
				}

				try {
					$commentItem->save();
				} catch (Exception $e){
					Mage::log($e);
				}

//				$quote->save();
//				$quote->sendOrderUpdateEmail($notify, $comment);
				$this->loadLayout();
				$response = $this->getLayout()->getBlock('comments')->toHtml();
			}
			catch (Mage_Core_Exception $e) {
				$response = array(
					'error'     => true,
					'message'   => $e->getMessage(),
				);
			}
			catch (Exception $e) {
				$response = array(
					'error'     => true,
					'message'   => $this->__('Cannot add order history.')
				);
			}
            if (is_array($response)) {
				$response = Mage::helper('core')->jsonEncode($response);
				$this->getResponse()->setBody($response);
			}
		$this->getResponse()->setBody($response);
        }

	public function printQuoteAction(){
		$quoteid = $this->getRequest()->getParam('quote_id');
		$quote = Mage::getModel('request4quote/quote')->loadByIdWithoutStore($quoteid);
		Mage::getModel('request4quote/quotepdf')->renderRfqRequest($quote);
		exit;
	}

}