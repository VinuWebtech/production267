<?php
class ITwebexperts_Request4quote_Helper_Data extends Mage_Core_Helper_Abstract
{

    const PATH_ENABLE_SHIPPING_QUOTES = 'request4quote/general/enable_shipping_quotes_customer_side';
    const PATH_ENABLE_TAX_ESTIMATES = 'request4quote/general/enable_tax_estimates';

    const PATH_SHOW_BILLING_ADDRESS_FOR_QUOTE_CUSTOMER = 'request4quote/billing_address/show_billing_address_for_quote_customer';
    const PATH_REQUIRE_BILLING_ADDRESS_FOR_QUOTE_CUSTOMER = 'request4quote/billing_address/require_billing_address_for_quote_customer';
    const PATH_SHOW_BILLING_ADDRESS_FOR_QUOTE_ADMIN = 'request4quote/billing_address/show_billing_address_for_quote_admin';
    const PATH_REQUIRE_BILLING_ADDRESS_FOR_QUOTE_ADMIN = 'request4quote/billing_address/require_billing_address_for_quote_admin';

    const PATH_SHOW_SHIPPING_ADDRESS_FOR_QUOTE_CUSTOMER = 'request4quote/shipping_address/show_shipping_address_for_quote_customer';
    const PATH_REQUIRE_SHIPPING_ADDRESS_FOR_QUOTE_CUSTOMER = 'request4quote/shipping_address/require_shipping_address_for_quote_customer';
    const PATH_SHOW_SHIPPING_ADDRESS_FOR_QUOTE_ADMIN = 'request4quote/shipping_address/show_shipping_address_for_quote_admin';
    const PATH_REQUIRE_SHIPPING_ADDRESS_FOR_QUOTE_ADMIN = 'request4quote/shipping_address/require_shipping_address_for_quote_admin';
    const PATH_SHOW_ACCEPTREJECT = 'request4quote/general/enable_acceptreject';
    const PATH_ATTACHMENTS = 'request4quote/quote_settings/attach_pdf';
    const PATH_PDFCOMMENTS = 'request4quote/quote_settings/show_comments';
    const PATH_DEFAULT_PROCESSED = 'request4quote/general/auto_select_status';
    const PATH_DEFAULT_NEW_ADMIN = 'request4quote/general/auto_select_status_admin';
    const PATH_DISABLE_REDIRECT = 'request4quote/general/disable_redirect';
    const PATH_DISABLE_REDIRECT_LISTING = 'request4quote/general/disable_redirect_listing';
    const PATH_COMMENTSIMPORT = 'request4quote/general/importcomments';
    const PATH_SHOWREGULARPRICE = 'request4quote/general/showregularprice';
    const PATH_SHOWNAV = 'request4quote/general/shownav';
    const PATH_MINICART = 'request4quote/general/miniquote';
    const PATH_MINICARTCLASS = 'request4quote/general/miniquoteclass';
    const PATH_CHANGEQUANTITY = 'request4quote/general/allowchangequantity';
    const PATH_CHANGESHIPMETHOD = 'request4quote/shipping_address/allowchangeshipmethod';
    const PATH_CHANGESHIPADDRESS = 'request4quote/shipping_address/allowchangeshipaddress';
	
	protected $_isRentalInstalled;
    protected $_isOpenendedInstalled;
    protected $_isPPRWarehouseInstalled;

    /**
     * Check if quote address is in customer address book, if not add it
     */
    public function checkAndCopyAddress($quoteid)
    {
        $quote = Mage::getModel('request4quote/quote')->loadByIdWithoutStore($quoteid);
        $customerid = $quote->getCustomerId();
        $quoteAddress = $quote->getShippingAddress();
        // Go through customer address book, check for match
        $customer = Mage::getModel('customer/customer')->load($customerid);
        if (is_object($customer)) {
            $customerAddresses = $customer->getAddresses();
            foreach ($customerAddresses as $customeraddress) {
                if ($this->sameAddress($quoteAddress, $customeraddress)) {
                    return;
                }
            }
            // if no match we need to add the quote address to customer address book
            $addressToAdd = $this->convertQuoteToCustomerAddress($quoteAddress);
            $address = Mage::getModel('customer/address');
            $address->setData($addressToAdd);
            $address->setCustomerId($customerid);
            $address->setSaveInAddressBook('1');
            try {
                $address->save();
            } catch (Exception $e) {
            }
            //if(!Mage::getSingleton('customer/session')->isLoggedIn()) {
            //Mage::getSingleton('customer/session')->loginById($customerid);
            //}

        }
    }

    /**
     * Converts quote address to array that can be added to a customer address
     */
    public function convertQuoteToCustomerAddress($addressToConvert){
        $addressData =  array (
                    'prefix' => $addressToConvert->getPrefix(),
                    'firstname' => $addressToConvert->getFirstname(),
                    'middlename' => $addressToConvert->getMiddlename(),
                    'lastname' => $addressToConvert->getLastname(),
                    'suffix' => $addressToConvert->getSuffix(),
                    'company' => $addressToConvert->getCompany(),
                    'street' => $addressToConvert->getStreet(),
                    'city' => $addressToConvert->getCity(),
                    'country_id' => $addressToConvert->getCountryId(),
                    'region' => $addressToConvert->getRegion(),
                    'region_id' => $addressToConvert->getRegionId(),
                    'postcode' => $addressToConvert->getPostcode(),
                    'telephone' => $addressToConvert->getTelephone(),
                    'fax' => $addressToConvert->getFax(),
                    'is_default_billing' => 0,
                    'is_default_shipping' => 0
                );
        return $addressData;
    }

    /**
     * Check if 2 addresses are the same
     */
    public function sameAddress($address1,$address2){
        if(
        $address1->getLastname() == $address2->getLastname() &&
        $address1->getStreet() == $address2->getStreet()&&
        $address1->getCity() == $address2->getCity() &&
        $address1->getPostcode() == $address2->getPostcode()
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Allowed to change cart quantity from item added from quote?
     */
    public function allowChangeQuantity(){
        return Mage::getStoreConfig(self::PATH_CHANGEQUANTITY);
    }

    /**
     * Allowed to change ship method on cart with quote items?
     */
    public function allowChangeShipMethod(){
        return Mage::getStoreConfig(self::PATH_CHANGESHIPMETHOD);
    }

    /**
     * Allowed to change shipping address on cart with quote items?
     */
    public function allowChangeShipAddress(){
        return Mage::getStoreConfig(self::PATH_CHANGESHIPADDRESS);
    }

    /**
     * Checks if miniquote is enabled
     */

    public function miniquoteEnabled(){
        return (bool)Mage::getStoreConfig(self::PATH_MINICART);
    }

    /**
     * Checks if cart has quote items in it, if so returns true
     * this is used by lock quantity and lock shipping address
     * and method
     */

    public function cartHasQuoteItems(){
        $cart = Mage::getSingleton('checkout/cart')->getQuote();
        foreach ($cart->getAllItems() as $item) {
            $buyrequest = $item->getOptionByCode('info_buyRequest');
            $buyrequest = new Varien_Object(unserialize($buyrequest->getValue()));
            if($buyrequest->getData(ITwebexperts_Request4quote_Model_Quote::QUOTE_ID_OPTION)){
                return $buyrequest->getData(ITwebexperts_Request4quote_Model_Quote::QUOTE_ID_OPTION);
            }
        }
        return false;
    }

    /**
     * Setting: show navigation when quote is empty
     *
     * @return bool
     */
    public function enabledShowNav(){
        return (bool)Mage::getStoreConfig(self::PATH_SHOWNAV);
    }

    public function enabledShowRegularPrice(){
        return (bool)Mage::getStoreConfig(self::PATH_SHOWREGULARPRICE);
    }

    /**
     * Checks if price of product should be hidden from customer based on product
     * show price setting and show price to customer group setting
     *
     * @param $product
     * @return bool
     */

    public function isPriceHidden($product){
        $customerGroupId =  (int)Mage::getSingleton('customer/session')->getCustomerGroupId();

        $productGroupsToShow = (array)Mage::helper('itwebcommon')->getAttributeCodeForId($product->getId(),'r4q_showprice_group');
        $productHidePrice = Mage::helper('itwebcommon')->getAttributeCodeForId($product->getId(),'r4q_hide_price');
        $productGroupsToShow = array_map( 'intval', array_filter($productGroupsToShow, 'is_numeric' ));

        if($productHidePrice
            && (!in_array($customerGroupId,$productGroupsToShow,true))  // Customer group is not allowed price
            && (!in_array(100000,$productGroupsToShow,true))) // Is not shown to all customer groups group 0
        {
            return true;
        } else {return false;}
    }

    public function enabledAttachments(){
        return (bool)Mage::getStoreConfig(self::PATH_ATTACHMENTS);
    }

    public function enabledPdfComments(){
        return (bool)Mage::getStoreConfig(self::PATH_PDFCOMMENTS);
    }

    public function enabledImportComments(){
        return (bool)Mage::getStoreConfig(self::PATH_COMMENTSIMPORT);
    }


    public function isRentalInstalled()
	{
        if (is_null($this->_isRentalInstalled)) {
			$this->_isRentalInstalled = Mage::helper('core')->isModuleEnabled('ITwebexperts_Payperrentals');
		}
		return $this->_isRentalInstalled;
	}
    public function isOpenendedInstalled()
    {
        if (is_null($this->_isOpenendedInstalled)) {
            $modules = (array)Mage::getConfig()->getNode('modules')->children();
            if (isset($modules['ITwebexperts_Openendedinvoices'])) {
                $this->_isOpenendedInstalled = true;
            } else {
                $this->_isOpenendedInstalled = false;
            }
        }
        return $this->_isOpenendedInstalled;
    }
    public function isPPRWarehouseInstalled()
    {
        return $this->hasWarehouse();
    }
	
	public function getQuoteCode($quote) {
		return substr(sha1($quote->getId()), 0, 6);
	}

    /**
     * Shows drop down of quotes for adding to current quote
     *
     * @return string
     */
    public function getDropdownQuotes(){
        $quoteCollection = Mage::getModel('request4quote/quote')->getCollection()
        ->addFieldToFilter('customer_email', Mage::getSingleton('customer/session')->getCustomer()->getEmail())
        ->setOrder('created_at');
        $dd = '<select name="r4quote" style="float:right;margin-left:3px;"><option value="new">--'.Mage::helper('request4quote')->__('Current Quote').'--</option>';
        foreach($quoteCollection as $iquote){
            $quoteDate = Mage::helper('core')->formatDate($iquote->getCreatedAt());
            $dd .= '<option value="'.$iquote->getId().'">' . Mage::helper('request4quote')->__('Quote #') .$iquote->getId().' - '.$quoteDate.'</option>';
        }
        $dd .= '</select>';
        return $dd;
    }



    /**
     * Returns true if shipping quotes are enabled.
     * @return bool
     */
    public function isShippingQuotesEnabled()
    {
        //return false;
        return (bool)Mage::getStoreConfig(self::PATH_ENABLE_SHIPPING_QUOTES);
    }

    /**
     * Returns true if tax estimates enabled.
     * @return bool
     */
    public function isTaxEstimatesEnabled()
    {
       // return false;
        return (bool)Mage::getStoreConfig(self::PATH_ENABLE_TAX_ESTIMATES);
    }

    /**
     * Returns true if setting to use accept and reject quote step is enabled
     *
     * @return bool
     */

    public function isAcceptRejectEnabled()
    {
        // return false;
        return (bool)Mage::getStoreConfig(self::PATH_SHOW_ACCEPTREJECT);
    }

    /**
     * Returns true if billing address can be show to the customer.
     * @return bool
     */
    public function canShowBillingAddressCustomer()
    {
        return (bool)Mage::getStoreConfig(self::PATH_SHOW_BILLING_ADDRESS_FOR_QUOTE_CUSTOMER);
    }

    /**
     * Returns true if billing address enabled for quote (customer side).
     * @return bool
     */
    public function isBillingAddressRequiredCustomer()
    {
        return (bool)Mage::getStoreConfig(self::PATH_REQUIRE_BILLING_ADDRESS_FOR_QUOTE_CUSTOMER);
    }

    /**
     * Returns true if billing address can be show to the admin.
     * @return bool
     */
    public function canShowBillingAddressAdmin()
    {
        return (bool)Mage::getStoreConfig(self::PATH_SHOW_BILLING_ADDRESS_FOR_QUOTE_ADMIN);
    }

    /**
     * Returns true if billing address enabled for quote (admin side).
     * @return bool
     */
    public function isBillingAddressRequiredAdmin()
    {
        return (bool)Mage::getStoreConfig(self::PATH_REQUIRE_BILLING_ADDRESS_FOR_QUOTE_ADMIN);
    }

    /**
     * Returns true if shipping address can be show to the customer.
     * @return bool
     */
    public function canShowShippingAddressCustomer()
    {
        return (bool)Mage::getStoreConfig(self::PATH_SHOW_SHIPPING_ADDRESS_FOR_QUOTE_CUSTOMER);
    }

    /**
     * Returns true if shipping address enabled for quote (customer side).
     * @return bool
     */
    public function isShippingAddressRequiredCustomer()
    {
        return (bool)Mage::getStoreConfig(self::PATH_REQUIRE_SHIPPING_ADDRESS_FOR_QUOTE_CUSTOMER);
    }

    /**
     * Returns true if shipping address can be show to the admin.
     * @return bool
     */
    public function canShowShippingAddressAdmin()
    {
        return (bool)Mage::getStoreConfig(self::PATH_SHOW_SHIPPING_ADDRESS_FOR_QUOTE_ADMIN);
    }

    /**
     * Returns true if shipping address enabled for quote (admin side).
     * @return bool
     */
    public function isShippingAddressRequiredAdmin()
    {
        return (bool)Mage::getStoreConfig(self::PATH_REQUIRE_SHIPPING_ADDRESS_FOR_QUOTE_ADMIN);
    }

    /**
     * Checks if 'ITwebexperts_PPRWarehouse' module exist and enabled in global config.
     *
     * @return boolean
     */
    public function hasWarehouse()
    {
        return Mage::helper('core')->isModuleEnabled('ITwebexperts_PPRWarehouse');
    }

    /**
     * Removes js validation
     *
     * @param string $html
     * @return string
     */
    public function removeRequired($html)
    {
        //$html = str_replace('<span class="required">*</span>', '', $html);
        //$html = str_replace('required-entry', '', $html);
        return $html;//str_replace('required', '', $html);
    }


    public function getRequest4quoteUrl($product, $additional = array())
    {
        return Mage::helper('request4quote/cart')->getAddUrl($product, $additional);
    }

    public function getDefaultProcessedStatus(){
        return Mage::getStoreConfig(self::PATH_DEFAULT_PROCESSED);
    }

    public function getDefaultNewQuoteStatus(){
        return Mage::getStoreConfig(self::PATH_DEFAULT_NEW_ADMIN);
    }

    /**
     * Returns a list of quote statuses as options for a form
     * if you pass in a current quote status it will be selected
     *
     * @param $currentStatus current status from quote
     * @return string
     */

    public function getStatusOptions($currentStatus){
        $statusCollection = Mage::getModel('request4quote/quote_status')->getCollection();
        $statusOptions = '';
        $showprice = $this->__(' - Shows Prices');
        $hideprice = $this->__(' - Hides Prices');
        foreach ($statusCollection AS $status){
            if(
            // Set selected status to saved quote status unless it's new
            ($status->getStatus() == $currentStatus && $currentStatus != 'new')
            // if status is new, set it to what the setting is for default processed status
            || ($currentStatus == 'new' && $status->getStatus() == $this->getDefaultProcessedStatus())
            || (!$currentStatus && $status->getStatus() == $this->getDefaultNewQuoteStatus()))
            {
                $selected = 'selected';
            } else {$selected = '';}
            if($status->getAllowviewcheckout()){
                $pricetext = $showprice;
            } else {$pricetext = $hideprice;}
        $statusOptions .= "<option value=\"{$status->getStatus()}\" $selected>{$status->getLabel()}{$pricetext}</option>";
        }
        return $statusOptions;
    }

    /**
     * Can be used to check if a certain quote status allows showing prices and checkout button
     *
     * @param $quoteStatus
     * @return bool
     */

    public function showPricesandConfirm($quoteStatus){
        $statusModel = Mage::getModel('request4quote/quote_status')->load($quoteStatus);
        if($statusModel->getAllowviewcheckout()){
            return true;
        } else {return false;}
    }

    /**
     * Adds product start and end dates if they exist to quote start and end date fields
     *
     * @param $quote
     * @param $product
     */

    public function saveStartEndDatesToQuote($quote,$product){
        $startDate = null;
        $endDate = null;
        if(is_object($product->getCustomOption('start_date'))) {
            $startDate = $product->getCustomOption('start_date')->getValue();
        }
        if(is_object($product->getOptionByCode('start_date'))) {
            $startDate = $product->getOptionByCode('start_date')->getValue();
        }
        if(is_object($product->getCustomOption('end_date'))) {
            $endDate = $product->getCustomOption('end_date')->getValue();
        }
        if(is_object($product->getOptionByCode('end_date'))) {
            $endDate = $product->getOptionByCode('end_date')->getValue();
        }
        if($startDate){
            $quote->setStartDatetime($startDate);
        }
        if($endDate){
            $quote->setEndDatetime($endDate);
        }
        if($startDate || $endDate){
            $quote->save();
        }
    }

    public function hasAdminShipping(){
        return Mage::helper('core')->isModuleEnabled('ITwebexperts_AdminShipping');
    }

}

