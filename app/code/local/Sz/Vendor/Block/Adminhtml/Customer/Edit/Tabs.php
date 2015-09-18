<?php
class Sz_Vendor_Block_Adminhtml_Customer_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct(){
        parent::__construct();
        $this->setId('customer_info_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('customer')->__('Customer Information'));
    }

    protected function _beforeToHtml(){
        $this->addTab('account', array(
            'label'     => Mage::helper('customer')->__('Account Information'),
            'content'   => $this->getLayout()->createBlock('adminhtml/customer_edit_tab_account')->initForm()->toHtml(),
            'active'    => Mage::registry('current_customer')->getId() ? false : true
        ));
        $this->addTab('addresses', array(
            'label'     => Mage::helper('customer')->__('Addresses'),
            'content'   => $this->getLayout()->createBlock('adminhtml/customer_edit_tab_addresses')->initForm()->toHtml(),
        ));
        $isPartner= Mage::getModel('vendor/userprofile')->isPartner();
        if (Mage::registry('current_customer')->getId()) {
            if($isPartner == Sz_Vendor_Model_Userprofile::IS_ONLY_CUSTOMER){
                if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
                    $this->addTab('orders', array(
                        'label'     => Mage::helper('customer')->__('Orders'),
                        'class'     => 'ajax',
                        'url'       => $this->getUrl('*/*/orders', array('_current' => true)),
                     ));
                }
                $this->addTab('cart', array(
                    'label'     => Mage::helper('customer')->__('Shopping Cart'),
                    'class'     => 'ajax',
                    'url'       => $this->getUrl('*/*/carts', array('_current' => true)),
                ));
                $this->addTab('wishlist', array(
                    'label'     => Mage::helper('customer')->__('Wishlist'),
                    'class'     => 'ajax',
                    'url'       => $this->getUrl('*/*/wishlist', array('_current' => true)),
                ));
                if (Mage::getSingleton('admin/session')->isAllowed('newsletter/subscriber')) {
                    $this->addTab('newsletter', array(
                        'label'     => Mage::helper('customer')->__('Newsletter'),
                        'content'   => $this->getLayout()->createBlock('adminhtml/customer_edit_tab_newsletter')->initForm()->toHtml()
                    ));
                }
                if (Mage::getSingleton('admin/session')->isAllowed('catalog/reviews_ratings')) {
                    $this->addTab('reviews', array(
                        'label'     => Mage::helper('customer')->__('Product Reviews'),
                        'class'     => 'ajax',
                        'url'       => $this->getUrl('*/*/productReviews', array('_current' => true)),
                    ));
                }
                if (Mage::getSingleton('admin/session')->isAllowed('catalog/tag')) {
                    $this->addTab('tags', array(
                        'label'     => Mage::helper('customer')->__('Product Tags'),
                        'class'     => 'ajax',
                        'url'       => $this->getUrl('*/*/productTags', array('_current' => true)),
                    ));
                }
            } else {

                if($isPartner==1){
                    $this->addTab('payment', array(
                        'label'     => Mage::helper('customer')->__('Payment Information'),
                        'content'   => $this->paymentmode(),
                    ));
                    
                    $this->addTab('vendorcommision', array(
                        'label'     => Mage::helper('customer')->__("Vendor Commission"),
                        'content'   => $this->vendorcommision(),
                    ));
                    $this->addTab('newproduct', array(
                        'label'     => Mage::helper('customer')->__("Add Product"),
                        'content'   => $this->addproduct(),
                    ));
                    $this->addTab('removeproduct', array(
                        'label'     => Mage::helper('customer')->__("Remove Product"),
                        'content'   => $this->removeproduct(),
                    ));
                    $this->addTab('notpartner', array(
                        'label'     => Mage::helper('customer')->__("Do You Want To Remove This Vendor?"),
                        'content'   => $this->removepartner(),
                    ));
                }
                else {
                $this->addTab('partner', array(
                        'label'     => Mage::helper('customer')->__('Do You Want To Make This Customer As Vendor?'),
                        'content'   => $this->wantpartner(),
                    ));
                }
                $this->removeTab('customer_edit_tab_agreements');
                $this->removeTab('customer_edit_tab_recurring_profile');
            }
			
        }
        $this->_updateActiveTab();
        Varien_Profiler::stop('customer/tabs');
        return parent::_beforeToHtml();
    }	
	protected function paymentmode(){	
		$row =Mage::getModel('vendor/userprofile')->getpaymentmode();
		if($row!=''){	
			return '<div class="entry-edit">
						<div class="entry-edit-head">
							<h4 class="icon-head head-customer-view">Payment Details</h4>
						</div>
						<fieldset>
							<address>
								<strong>'.$row.'</strong><br>
							</address>
						</fieldset>
					</div>';
		}
		else{
			return '<div class="entry-edit">
							<div class="entry-edit-head">
								<h4 class="icon-head head-customer-view">Payment Details</h4>
							</div>
							<fieldset>
								<address>
									<strong>Not Mentioned Yet</strong><br>
								</address>
							</fieldset>
						</div>';
		}
	
	}
	
	protected function vendorinformation(){
		$helper = Mage::helper('vendor');
		$id=Mage::registry('current_customer')->getId();	
		$partner=Mage::getModel('vendor/userprofile')->getPartnerProfileById($id);
		$options ='';
		foreach(Mage::getModel('directory/country')->getResourceCollection()->loadByStore()->toOptionArray(true) as $country){
			if($country['value']!=''){
				$selectedval = $partner['countrypic']==$country['value']?"selected='selected'":"";
			}
			if($country['value'])
				$options = $options.'<option '.$selectedval.' value="'.$country['value'].'">'.$country['label'].'</option>';
		} 
		$html = '<style>#wkvendorinfo .form-list td.value input.input-text, #wkvendorinfo .form-list td.value textarea{width:100%!important;}#wkvendorinfo .defaultSkin table.mceToolbar{float:left} #wkvendorinfo .defaultSkin table.mceLayout{width:100%!important;}</style>
		<script language="javascript" type="text/javascript" src="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS).'/tiny_mce/tiny_mce.js'.'"></script>
			<script type="text/javascript">
			//< ![CDATA[
			Event.observe(window, "load", function() {
			tinyMCE.init({
			mode : "exact",
			theme : "advanced",
			elements : "compdesi",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_path_location : "bottom",
			extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
			theme_advanced_resize_horizontal : "false",
			theme_advanced_resizing : "false",
			apply_source_formatting : "true",
			convert_urls : "false",
			force_br_newlines : "false",
			JustifyFull : "true",
			doctype : \'< !DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">\'
			});
			tinyMCE.init({
			mode : "exact",
			theme : "advanced",
			elements : "returnpolicy",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_path_location : "bottom",
			extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
			theme_advanced_resize_horizontal : "false",
			theme_advanced_resizing : "false",
			apply_source_formatting : "true",
			convert_urls : "false",
			force_br_newlines : "true",
			doctype : \'< !DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">\'
			});
			tinyMCE.init({
			mode : "exact",
			theme : "advanced",
			elements : "shippingpolicy",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_path_location : "bottom",
			extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
			theme_advanced_resize_horizontal : "false",
			theme_advanced_resizing : "false",
			apply_source_formatting : "true",
			convert_urls : "false",
			force_br_newlines : "true",
			doctype : \'< !DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">\'
			});
			});
			 
			//]]>
			</script>';
		return $html.'<div class="entry-edit" id="wkvendorinfo">
					<div class="entry-edit-head">
						<h4 class="icon-head head-customer-view">'.$helper->__('Vendor Information').'</h4>
					</div>
					<fieldset>
						<table cellspacing="0" class="form-list">
				            <tbody>
								<tr>
							        <td class="label"><label>'.$helper->__('Twitter ID').'</label></td>
								    <td class="value">
								        <input name="twitterid" class="input-text" type="text" value="'.$partner['twitterid'].'">
								    </td>
							    </tr>
							    <tr>
							        <td class="label"><label>'.$helper->__('Facebook ID').'</label></td>
								    <td class="value">
								        <input name="facebookid" class="input-text" type="text" value="'.$partner['facebookid'].'">
								    </td>
							    </tr>
							    <tr>
							        <td class="label"><label>'.$helper->__('Contact Number').'</label></td>
								    <td class="value">
								        <input name="contactnumber validate-phoneStrict" class="input-text" type="text" value="'.$partner['contactnumber'].'">
								    </td>
							    </tr>
							    <tr>
							        <td class="label"><label>'.$helper->__('Shop Title').'</label></td>
								    <td class="value">
								        <input name="shoptitle" class="input-text" type="text" value="'.$partner['shoptitle'].'">
								    </td>
							    </tr>
							    <tr>
							        <td class="label"><label>'.$helper->__('Company Locality').'</label></td>
								    <td class="value">
								        <input name="complocality" class="input-text" type="text" value="'.$partner['complocality'].'">
								    </td>
							    </tr>
							    <tr>
							        <td class="label"><label>'.$helper->__('Country').'</label></td>
								    <td class="value">
								        <select name="countrypic" id="countrypic">
											<option value="" selected="selected" disabled="disabled">'.$helper->__("Select Country").'</option>'.$options.'</select>
								    </td>
							    </tr>
							    <tr>
							        <td class="label"><label>'.$helper->__('Company Description').'</label></td>
								    <td class="value">
								        <textarea type="text" id="compdesi" name="compdesi" title="'.$helper->__('Company Description').'" class="input-text" >'.$partner['compdesi'].'</textarea>
								    </td>
							    </tr>
							    <tr>
							        <td class="label"><label>'.$helper->__('Return Policy').'</label></td>
								    <td class="value">
								    	<textarea type="text" id="returnpolicy" name="returnpolicy" title="'.$helper->__('Return Policy').'" class="input-text" >'.$partner['returnpolicy'].'</textarea>
								    </td>
							    </tr>
							    <tr>
							        <td class="label"><label>'.$helper->__('Shipping Policy').'</label></td>
								    <td class="value">
								    	<textarea type="text" id="shippingpolicy" name="shippingpolicy" title="'.$helper->__('Shipping Policy').'" class="input-text" >'.$partner['shippingpolicy'].'</textarea>
								    </td>
							    </tr>
							    <tr>
							        <td class="label"><label>'.$helper->__('Meta Keywords').'</label></td>
								    <td class="value">
								    	<textarea type="text" id="meta_keywords" name="meta_keyword" title="Meta Keyword" class="input-text" >'.$partner['meta_keyword'].'</textarea>
								    </td>
							    </tr>
							    <tr>
							        <td class="label"><label>'.$helper->__('Meta Description').'</label></td>
								    <td class="value">
								    	<textarea type="text" id="meta_keywords" name="meta_description" title="Meta Description" class="input-text" >'.$partner['meta_description'].'</textarea>
								    </td>
							    </tr>
							    <tr>
							        <td class="label"><label>'.$helper->__('Company Banner').'</label></td>
								    <td class="value">
								        <input name="bannerpic" class="input-text" type="file">
								        <img style="margin:5px 0;width:700px;heigth:200px;" src="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'avatar/'.$partner['bannerpic'].'"/>
								    </td>
							    </tr>
							    <tr>
							        <td class="label"><label>'.$helper->__('Company Logo').'</label></td>
								    <td class="value">
								        <input name="logopic" class="input-text" type="file">
								        <div><img style="margin:5px 0;width:100px;" src="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'avatar/'.$partner['logopic'].'"/></div>
								    </td>
							    </tr>
				            </tbody>
				        </table>
					</fieldset>
				</div>';
	}

	protected function vendorcommision(){
		$id=Mage::registry('current_customer')->getId();	
		$collection = Mage::getModel('vendor/saleperpartner')->getCollection();
		$collection->addFieldToFilter('mageuserid',array('eq'=>$id));	
		if(count($collection)) {
		  foreach ($collection as $value) {
				$rowcom = $value->getCommision();
			}	
		}
		else
		{
		  $rowcom =Mage::getStoreConfig('vendor/vendor_options/percent'); 
		}
		$tsale=0;
		$tcomm=0;
		$tact=0;
		$collection1 = Mage::getModel('vendor/saleslist')->getCollection();
		$collection1->addFieldToFilter('mageproownerid',array($id));	
		foreach ($collection1 as $key) {
				$tsale+=$key->gettotalamount();
				$tcomm+=$key->gettotalcommision();
				$tact+=$key->getactualparterprocost();
			}		
	
		//	if($rowcom>0) { $comm = $rowcom; } elseif(Mage::getStoreConfig('vendor/vendor_options/percent')>0) { $comm=Mage::getStoreConfig('vendor/vendor_options/percent');} else { $comm = 20;}
	    $comm = $rowcom;
		return '<div class="entry-edit">
					<div class="entry-edit-head">
						<h4 class="icon-head head-customer-view">Commission Details</h4>
					</div>
					<fieldset>
						Set Commision In Percentage For This Particular Vendor : <input name="commision" type="text" classs="input-text no-changes" value="'.$rowcom.'"/>
						<table class="grid table" id="customer_cart_grid1_table" >
							<tr>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td>Total Sale</td>
								<td>'.Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol().' '.$tsale.'</td>
							</tr>
							<tr>
								<td>Total Vendor Sale</td>
								<td>'.Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol().' '.$tact.'</td>
							</tr>
							<tr>
								<td>Total Admin Sale</td>
								<td>'.Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol().' '.$tcomm.'</td>
							</tr>
							<tr>
								<td>Current Commision %</td>
								<td>'.$comm.'%</td>
							</tr>
						</table>
					</fieldset>							
				</div>';
	}

	protected function addproduct(){
		$customerId=Mage::registry('current_customer')->getId();
		$coll=Mage::getModel('vendor/product')->getCollection()
						->addFieldToFilter('userid',array('eq'=>$customerId))
						->addFieldToFilter('adminassign',array('eq'=>1));
		$productids=array();
		foreach($coll as $row){
			array_push($productids, $row->getMageproductid());
		}
		if(count($productids))
			$proids= implode(',', $productids);
		else
			$proids= 'none';
		$html='<div class="entry-edit">
					<div class="entry-edit-head">
						<h4 class="icon-head head-customer-view">Assign Product To Vendor</h4>
					</div>
					<fieldset>
						Enter Product ID : <input name="vendorassignproid" type="text" classs="input-text no-changes" value=""/>	&nbsp;&nbsp;&nbsp;&nbsp;<b>Notice: Enter Only Integer value by comma(,)</b>
					</fieldset>
					<fieldset>
						Assigned Product Ids : '.$proids.'
					</fieldset>
				</div>';
		return $html;
	}

	protected function removeproduct(){
		$html='<div class="entry-edit">
					<div class="entry-edit-head">
						<h4 class="icon-head head-customer-view">Unassign Product To Vendor</h4>
					</div>
					<fieldset>
						Enter Product ID : <input name="vendorunassignproid" type="text" classs="input-text no-changes" value=""/>	&nbsp;&nbsp;&nbsp;&nbsp;<b>Notice: Enter Only Integer value by comma(,)</b>';
		$html.= '</fieldset></div>';
		return $html;
	}	
	
	protected function wantpartner(){	
		$customerId=Mage::registry('current_customer')->getId();
		$coll=Mage::getModel('vendor/userprofile')->getCollection();
		$coll->addFieldToFilter('mageuserid',array('eq'=>$customerId));
		$profileurl='';
		foreach($coll as $row){
			$profileurl=$row->getProfileurl();
		}
		return '<div class="entry-edit">
					<div class="entry-edit-head">
						<h4 class="icon-head head-customer-view">Do You Want To Make This Customer As Vendor?</h4>
					</div>
					<fieldset>
						<input type="checkbox" name="partnertype" value="1">&nbsp;Approve Vendor
					</fieldset>							
				</div>
				<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
				<script>
					var $wk_jq= jQuery.noConflict();
					$wk_jq(function(){
						$wk_jq(".profileurl").keyup(function(){
							$wk_jq(this).val($wk_jq(this).val().replace(/[^a-z^0-9\.]/g,""));
						});
					});
				</script>';
	}
	
	protected function removepartner(){	
		return '<div class="entry-edit">
					<div class="entry-edit-head">
						<h4 class="icon-head head-customer-view">Do You Want To Remove This Vendor?</h4>
					</div>
					<fieldset>
						<input type="checkbox" name="partnertype" value="2">&nbsp;Unapprove Vendor
					</fieldset>							
				</div>';
	}
    protected function _updateActiveTab(){
        $tabId = $this->getRequest()->getParam('tab');
        if( $tabId ) {
            $tabId = preg_replace("#{$this->getId()}_#", '', $tabId);
            if($tabId) {
                $this->setActiveTab($tabId);
            }
        }
    }
}
