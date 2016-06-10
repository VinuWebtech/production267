<?php
class ITwebexperts_Request4quote_Helper_Email extends Mage_Checkout_Helper_Cart {


	const RFQ_SENDER_NAME = 'request4quote/quote_settings/sender_name';
	const RFQ_SENDER_EMAIL = 'request4quote/quote_settings/sender_email';
	const RFQ_SENDER_SENDTO = 'request4quote/quote_settings/send_requests_to';
	const RFQ_EMAIL_QUOTE_SUBMITTED = 'request4quote/emails/quote_submitted';
	const RFQ_EMAIL_QUOTE_REJECTED = 'request4quote/emails/quote_rejected';
	const RFQ_EMAIL_QUOTE_ADMIN = 'request4quote/emails/quote_admin';
	const RFQ_EMAIL_QUOTE_ACCEPTED = 'request4quote/emails/quote_accepted';
	const RFQ_EMAIL_QUOTE_COMMENT_TOCUSTOMER = 'request4quote/emails/quote_commentcustomer';
	const RFQ_EMAIL_QUOTE_COMMENT_TOADMIN = 'request4quote/emails/quote_commentadmin';
    const RFQ_EMAIL_QUOTE_SUBMITTED_ADMIN = 'request4quote/emails/quote_submitted_admin';
	const RFQ_BCCTO = 'request4quote/quote_settings/bccto';

	/**
	 * Return email template variables from quoteid or quote
	 *
	 * @param $quoteId
	 * @return array
	 */
	public function getEmailVariables($quoteId){
		if(is_object($quoteId)){
			$quote = Mage::getModel('request4quote/quote')->loadByIdWithoutStore($quoteId->getId());
            $quoteId = $quoteId->getId();
		} else {
			$quote = Mage::getModel('request4quote/quote')->loadByIdWithoutStore($quoteId);
		}
		$emailTemplateVariables = array();
		$emailTemplateVariables['customer_name'] = $quote->getCustomerFirstname() . ' ' . $quote->getCustomerLastname();
		$shippingAddress = $quote->getShippingAddress();
		$street = $shippingAddress->getStreet();
		$emailTemplateVariables['email_address'] = $quote->getCustomerEmail();
		$emailTemplateVariables['customer_street'] = reset($street);
		$emailTemplateVariables['request_number'] = $quote->getId();
		$websites = Mage::app()->getWebsites();
		$storeId = $quote->getStoreId();
		$store = Mage::getModel('core/store')->load($storeId);
		if(!isset($store)){
			$storeId =  Mage::app()
				->getWebsite(true)
				->getDefaultGroup()
				->getDefaultStoreId();//quote should have store of the customer
			$store = Mage::getModel('core/store')->load($storeId);
		}
		$emailTemplateVariables['view_link'] = Mage::getModel('core/url')->setStore($store->getId())->getUrl('request4quote_front/quote/view', array(
			'token' => $quote->getR4qToken()
		));
        $emailTemplateVariables['comment'] = Mage::getModel('request4quote/comments')->getFirstComment($quoteId);
		$emailTemplateVariables['time'] = Mage::helper('core')->formatDate($quote->getCreatedAt(), 'medium', true);
		$emailTemplateVariables['address'] = $shippingAddress;
		$emailTemplateVariables['store'] = $store;
		$emailTemplateVariables['quote'] = $quote;
        $emailTemplateVariables['quoteid'] = $quote->getId();
		$emailTemplateVariables['handler'] = self::getItemsHandler();
		$emailTemplateVariables['send_to_name'] = $quote->getCustomerFirstname() . ' ' . $quote->getCustomerLastname();
		$emailTemplateVariables['view_link_admin'] = Mage::getUrl('adminhtml/quote/redirecttoquote', array('quote_id' => $quote->getId()));
		$emailTemplateVariables['adminname'] = Mage::getStoreConfig(self::RFQ_SENDER_NAME, $store->getId());
		return $emailTemplateVariables;
	}

	public function setEmailTemplate($storeId){
		$store = Mage::getModel('core/store')->load($storeId);
		if(!isset($store)){
			$storeId =  Mage::app()
				->getWebsite(true)
				->getDefaultGroup()
				->getDefaultStoreId();//quote should have store of the customer
			$store = Mage::getModel('core/store')->load($storeId);
		}
		if(!isset($store)){
			$store = $websites[1]->getDefaultStore();//quote should have store of the customer
		}
        /** @var $emailTemplate  Mage_Core_Model_Email_Template */
		$emailTemplate  = Mage::getModel('core/email_template');
		$emailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=> $store->getId()));
		return $emailTemplate;
	}

    private function getSender($storeId = null)
    {
        return array(
            'name'=>Mage::getStoreConfig(self::RFQ_SENDER_NAME, $storeId),
            'email'=>Mage::getStoreConfig(self::RFQ_SENDER_EMAIL, $storeId)
        );
    }

	/**
	 * attach file to email
	 * supported types: pdf
	 *
	 * @param        $file
	 * @param        $mailObj
	 *
	 * @return mixed
	 */
	public function addFileAttachment($filePath, $mailObj)
	{
		try {
			if (file_exists($filePath)) {
				$mailObj->getMail()->createAttachment(
					file_get_contents($filePath),
					'application/pdf',
					Zend_Mime::DISPOSITION_ATTACHMENT,
					Zend_Mime::ENCODING_BASE64,
					basename($filePath)
				);
			}
		} catch (Exception $e) {
			Mage::log('Caught error while attaching pdf:' . $e->getMessage());
		}
		return $mailObj;
	}
	
	public function getItemsHandler()
	{
		return Mage::helper('request4quote')->isRentalInstalled() ? 'request4quote_email_items_rental' : 'request4quote_email_items';
	}

    public function getRFQSenderTo($storeId = null){
        $rfqsenderto = Mage::getStoreConfig(self::RFQ_SENDER_SENDTO, $storeId);
        $rfqsenderto = preg_split('/\s*,\s*/', trim($rfqsenderto));
        //$rfqsenderto = explode(',',$rfqsenderto);
        return $rfqsenderto;
    }

	public function getRFQbccTo($storeId = null){
		$rfqbccto = Mage::getStoreConfig(self::RFQ_BCCTO, $storeId);
		if($rfqbccto) {
			$rfqbccto = preg_split('/\s*,\s*/', trim($rfqbccto));
			return $rfqbccto;
		} else {
			return false;
		}
	}

	/**
	 * Email sent to customer when customer submits quote request
	 *
	 * @param $quoteId
	 */

    public function sendRequestNotification($quoteId)
    {
		$quote = Mage::getModel('request4quote/quote')->loadByIdWithoutStore($quoteId);
		$emailTemplateVariables = $this->getEmailVariables($quoteId);
		$emailTemplate = $this->setEmailTemplate($quote->getStoreId());
        $emailTemplateId = Mage::getStoreConfig(self::RFQ_EMAIL_QUOTE_SUBMITTED, $emailTemplateVariables['store']->getId());
		$emailTemplate->sendTransactional($emailTemplateId,$this->getSender($emailTemplateVariables['store']->getId()),$quote->getCustomerEmail(), $emailTemplateVariables['send_to_name'], $emailTemplateVariables, $emailTemplateVariables['store']->getId());
    }

    /**
     * Email sent to admin when customer submits quote request
     *
     * @param $quoteId
     */

    public function sendRequestNotificationAdmin($quoteId)
    {
		if(is_object($quoteId)){
			$quote = Mage::getModel('request4quote/quote')->loadByIdWithoutStore($quoteId->getId());
		} else {
			$quote = Mage::getModel('request4quote/quote')->loadByIdWithoutStore($quoteId);
		}
        $emailTemplateVariables = $this->getEmailVariables($quoteId);
        $emailTemplate = $this->setEmailTemplate($quote->getStoreId());
        $emailTemplateId = Mage::getStoreConfig(self::RFQ_EMAIL_QUOTE_SUBMITTED_ADMIN, $emailTemplateVariables['store']->getId());
        $emailTemplate->sendTransactional($emailTemplateId,$this->getSender($emailTemplateVariables['store']->getId()),$this->getRFQSenderTo($emailTemplateVariables['store']->getId()), Mage::getStoreConfig(self::RFQ_SENDER_NAME, $emailTemplateVariables['store']->getId()), $emailTemplateVariables, $emailTemplateVariables['store']->getId());

    }

	/**
	 * Sends comment update email to customer from admin
	 *
	 * @param $quote
	 */

	public function sendCommentUpdate($quote, $emailcomment = null)
	{
		if(is_object($quote)){
			$quote = Mage::getModel('request4quote/quote')->loadByIdWithoutStore($quote->getId());
		} else {
			$quote = Mage::getModel('request4quote/quote')->loadByIdWithoutStore($quote);
		}
		$emailTemplate = $this->setEmailTemplate($quote->getStoreId());

		$emailTemplateVariables = $this->getEmailVariables($quote);
		if ($emailcomment) {
			$emailTemplateVariables['comment'] = $emailcomment;
		}

		$emailTemplateId = Mage::getStoreConfig(self::RFQ_EMAIL_QUOTE_COMMENT_TOCUSTOMER, $emailTemplateVariables['store']->getId());
		$emailTemplate->sendTransactional($emailTemplateId,$this->getSender($emailTemplateVariables['store']->getId()),$quote->getCustomerEmail(), $emailTemplateVariables['send_to_name'], $emailTemplateVariables, $emailTemplateVariables['store']->getId());
	}

	/**
	 * Sends comment update email to admin from customer side
	 *
	 * @param $quote
	 */

	public function sendCommentUpdateToadmin($quote, $emailcomment = null)
	{
		if(is_object($quote)){
			$quote = Mage::getModel('request4quote/quote')->loadByIdWithoutStore($quote->getId());
		} else {
			$quote = Mage::getModel('request4quote/quote')->loadByIdWithoutStore($quote);
		}
		$emailTemplate = $this->setEmailTemplate($quote->getStoreId());

		$emailTemplateVariables = $this->getEmailVariables($quote);
		if ($emailcomment) {
			$emailTemplateVariables['comment'] = $emailcomment;
		}
		$emailTemplateId = Mage::getStoreConfig(self::RFQ_EMAIL_QUOTE_COMMENT_TOADMIN, $emailTemplateVariables['store']->getId());
		$emailTemplate->sendTransactional($emailTemplateId,$this->getSender($emailTemplateVariables['store']->getId()),$this->getRFQSenderTo($emailTemplateVariables['store']->getId()), self::RFQ_SENDER_NAME, $emailTemplateVariables, $emailTemplateVariables['store']->getId());
	}

	/**
	 * Sends email with quoted prices to customer
	 *
	 * @param $quote
	 * @param $emailcomment are the quote comments
	 * @param $returnProcessed if set to true, this method returns the process email html
	 */
    
    public function sendRequestProposalNotification($quote, $emailcomment = null, $returnProcessed = false)
    {
		$attachpdf = Mage::helper('request4quote')->enabledAttachments();
		$showcommentspdf = Mage::helper('request4quote')->enabledPdfComments();

		$emailTemplate = $this->setEmailTemplate($quote->getStoreId());
		$emailTemplateVariables = $this->getEmailVariables($quote);
		if ($emailcomment) {
			$emailTemplateVariables['comment'] = '<br />'.Mage::helper('request4quote')->__('Comment: ').$emailcomment.'<br />';
		}
		try {
			if ($showcommentspdf) {
				$commentblock = Mage::app()->getLayout()->createBlock('request4quote/quote_comments', 'commentspdf')
					->setTemplate('request4quote/quote/comments.phtml');
				$commentblock->setData('quote_id', $quote->getId());
				$emailTemplateVariables['pdfcomment'] = $commentblock->toHtml();
			}
		}catch(Exception $e){

		}
		$emailTemplateId = Mage::getStoreConfig(self::RFQ_EMAIL_QUOTE_ADMIN, $emailTemplateVariables['store']->getId());
		/** Used for PDF */
		if($returnProcessed){
            /** if_numeric means is a custom email template and needs to load differently than regular */
            if (is_numeric($emailTemplateId)) {
                $emailTemplate->load($emailTemplateId);
            } else {
                $emailTemplate->loadDefault($emailTemplateId);
            }
			return $emailTemplate->getProcessedTemplate($emailTemplateVariables);
		}
		if($attachpdf){
			$pdffile = Mage::getModel('request4quote/quotepdf')->renderRfqRequest($quote, true);
			$emailTemplate = $this->addFileAttachment($pdffile, $emailTemplate);
		}
		if($this->getRFQbccTo($emailTemplateVariables['store']->getId())) {
			$emailTemplate->addBcc($this->getRFQbccTo($emailTemplateVariables['store']->getId()));
		}
		$emailTemplate->sendTransactional($emailTemplateId,$this->getSender($emailTemplateVariables['store']->getId()),$quote->getCustomerEmail(),  $emailTemplateVariables['send_to_name'], $emailTemplateVariables, $emailTemplateVariables['store']->getId());
    }

	/**
	 * Email sent when customer accepts quote it is sent to the admin
	 *
	 * @param $quote
	 */
	
	public function sendAcceptNotification($quote)
	{
		$emailTemplate = $this->setEmailTemplate($quote->getStoreId());
		$emailTemplateVariables = $this->getEmailVariables($quote);
        $emailTemplateId = Mage::getStoreConfig(self::RFQ_EMAIL_QUOTE_ACCEPTED, $emailTemplateVariables['store']->getId());
		$emailTemplate->sendTransactional($emailTemplateId,$this->getSender($emailTemplateVariables['store']->getId()),$this->getRFQSenderTo($emailTemplateVariables['store']->getId()), self::RFQ_SENDER_NAME, $emailTemplateVariables, $emailTemplateVariables['store']->getId());
	}

	/**
	 * Email sent when customer rejects quote - goes to admin
	 *
	 * @param $quote
	 */
	
	public function sendRejectNotification($quote)
	{
		$emailTemplate = $this->setEmailTemplate($quote->getStoreId());
		$emailTemplateVariables = $this->getEmailVariables($quote);
		$emailTemplateId = Mage::getStoreConfig(self::RFQ_EMAIL_QUOTE_REJECTED, $emailTemplateVariables['store']->getId());
		$emailTemplate->sendTransactional($emailTemplateId,$this->getSender($emailTemplateVariables['store']->getId()),$this->getRFQSenderTo($emailTemplateVariables['store']->getId()), self::RFQ_SENDER_NAME, $emailTemplateVariables, $emailTemplateVariables['store']->getId());
	}
}
