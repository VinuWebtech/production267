<?php
class Sz_Vendor_Adminhtml_OrderController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction() {
        $this->_title($this->__("Manage Vendor's Order"));
        $this->loadLayout()
            ->_setActiveMenu('vendor/vendor_order')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()
            ->renderLayout();
    }
    public function gridAction(){
        $this->loadLayout();
        $this->getResponse()->setBody($this->getLayout()->createBlock("vendor/adminhtml_order_grid")->toHtml());
    }
    public function masspayAction(){
        $wholedata=$this->getRequest()->getParams();
        $vendor_id = $this->getRequest()->getParam('vendor_id', 0);
        if (!$this->_validateFormKey()) {
             $this->_redirect('vendor/adminhtml_order/index',array('id'=>$vendor_id));
        }
        $actparterprocost = 0;
        $totalamount = 0;
        $orderinfo = '';
        $vendorOrderIds = $wholedata['vendororderids'];
        $wkvendororderids = array();
        $style='style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc";';
        $collection = Mage::getModel('vendor/saleslist')->getCollection()
                                ->addFieldToFilter('autoid',array('IN'=>$vendorOrderIds))
                                ->addFieldToFilter('paidstatus',array('eq'=>0))
                                ->addFieldToFilter('mageorderid',array('neq'=>0));
        foreach ($collection as $rowValue) {

            $row = Mage::getModel('vendor/saleslist')->load($rowValue->getAutoid());
            $row->setPaidstatus(1);
            $row->save();
            $wkvendororderids[] = $rowValue->getAutoid();
            $actparterprocost = $actparterprocost + $row->getActualparterprocost();
            $totalamount = $totalamount + $row->getTotalamount();
            $orderinfo = $orderinfo."<tr>
                                <td valign='top' align='left' ".$style.">".$row['magerealorderid']."</td>
                                <td valign='top' align='left' ".$style.">".$row['mageproname']."</td>
                                <td valign='top' align='left' ".$style.">".$row['magequantity']."</td>
                                <td valign='top' align='left' ".$style.">".Mage::app()->getStore()->formatPrice($row['mageproprice'])."</td>
                                <td valign='top' align='left' ".$style.">".Mage::app()->getStore()->formatPrice($row['totalcommision'])."</td>
                                <td valign='top' align='left' ".$style.">".Mage::app()->getStore()->formatPrice($row['actualparterprocost'])."</td>
                             </tr>";
        }
        if($actparterprocost){
            $collectionverifyread = Mage::getModel('vendor/saleperpartner')->getCollection();
            $collectionverifyread->addFieldToFilter('mageuserid',array('eq'=>$vendor_id));
            if(count($collectionverifyread)>=1){
                foreach($collectionverifyread as $verifyrow){
                    if($verifyrow->getAmountremain() >= $actparterprocost){
                        $totalremain=$verifyrow->getAmountremain()-$actparterprocost;
                    }
                    else{
                        $totalremain=0;
                    }
                    $verifyrow->setAmountremain($totalremain);
                    $verifyrow->save();
                    $totalremain;
                    $amountpaid=$verifyrow->getAmountrecived();
                    $totalrecived=$actparterprocost+$amountpaid;
                    $verifyrow->setAmountpaid($actparterprocost);
                    $verifyrow->setAmountrecived($totalrecived);
                    $verifyrow->setAmountremain($totalremain);
                    $verifyrow->save();
                }
            } else {
                $percent = Mage::getStoreConfig('vendor/vendor_options/percent');
                $collectionf=Mage::getModel('vendor/saleperpartner');
                $collectionf->setMageuserid($vendor_id);
                $collectionf->setTotalsale($totalamount);
                $collectionf->setAmountpaid($actparterprocost);
                $collectionf->setAmountrecived($actparterprocost);
                $collectionf->setAmountremain(0);
                $collectionf->setCommision($percent);
                $collectionf->save();
            }
            $unique_id = $this->checktransid();
            if($unique_id!=''){
                $vendor_trans = Mage::getModel('vendor/transaction')->getCollection()
                    ->addFieldToFilter('transactionid',array('eq'=>mysqli_real_escape_string($unique_id)));
                if(count($vendor_trans)){
                    foreach ($vendor_trans as $value) {
                        $id =$value->getId();
                        if($id){
                            Mage::getModel('vendor/transaction')->load($id)->delete();
                        }
                    }
                }
                $currdate = date('Y-m-d H:i:s');
                $vendor_trans = Mage::getModel('vendor/transaction');
                $vendor_trans->setTransactionid($unique_id);
                $vendor_trans->setTransactionamount($actparterprocost);
                $vendor_trans->setType('Manual');
                $vendor_trans->setMethod('Manual');
                $vendor_trans->setVendorid($vendor_id);
                $vendor_trans->setCustomnote($wholedata['customnote']);
                $vendor_trans->setCreatedAt($currdate);
                $transid = $vendor_trans->save()->getTransid();
            }

            foreach($wkvendororderids as $key){
                $collection = Mage::getModel('vendor/saleslist')->getCollection()
                    ->addFieldToFilter('autoid',array('eq'=>$key))
                    ->addFieldToFilter('cpprostatus',array('eq'=>1))
                    ->addFieldToFilter('paidstatus',array('eq'=>0))
                    ->addFieldToFilter('mageorderid',array('neq'=>0));
                foreach ($collection as $row) {
                    $row->setPaidstatus(1);
                    $row->setTransid($transid)->save();
                }
            }
        $vendor = Mage::getModel('customer/customer')->load($vendor_id);
        $emailTemp = Mage::getModel('core/email_template')->loadDefault('transactionmail');

        $emailTempVariables = array();
        $admin_storemail = Mage::getStoreConfig('vendor/vendor_options/adminemail');
        $adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
        $adminUsername = 'Admin';
        $emailTempVariables['myvar1'] = $vendor->getName();
        $emailTempVariables['myvar5'] = $orderinfo;
        $emailTempVariables['myvar6'] = $wholedata['customnote'];
        $processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
        $emailTemp->setSenderName($adminUsername);
        $emailTemp->setSenderEmail($adminEmail);
        $emailTemp->send($vendor->getEmail(),$vendor->getName(),$emailTempVariables);

        $this->_getSession()->addSuccess(Mage::helper('vendor')->__('Payment has been successfully done for this vendor'));
        }
    $this->_redirect('vendor/adminhtml_order/index',array('id'=>$vendor_id));
    }

    public function payvendorAction(){
        $wholedata=$this->getRequest()->getParams();
        $actparterprocost = 0;
        $totalamount = 0;
        $vendor_id = $wholedata['vendorid'];
        if (!$this->_validateFormKey()) {
             $this->_redirect('vendor/adminhtml_order/index',array('id'=>$vendor_id));
        }
        $orderinfo = '';
        $style='style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc";';
        $collection = Mage::getModel('vendor/saleslist')->getCollection()
                                ->addFieldToFilter('autoid',array('eq'=>$wholedata['autoorderid']))
                                ->addFieldToFilter('cpprostatus',array('eq'=>1))
                                ->addFieldToFilter('paidstatus',array('eq'=>0))
                                ->addFieldToFilter('mageorderid',array('neq'=>0));
        foreach ($collection as $row) {
            $actparterprocost = $actparterprocost + $row->getActualparterprocost();
            $totalamount = $totalamount + $row->getTotalamount();
            $vendor_id = $row->getMageproownerid();
            $orderinfo = $orderinfo."<tr>
                            <td valign='top' align='left' ".$style.">".$row['magerealorderid']."</td>
                            <td valign='top' align='left' ".$style.">".$row['mageproname']."</td>
                            <td valign='top' align='left' ".$style.">".$row['magequantity']."</td>
                            <td valign='top' align='left' ".$style.">".Mage::app()->getStore()->formatPrice($row['mageproprice'])."</td>
                            <td valign='top' align='left' ".$style.">".Mage::app()->getStore()->formatPrice($row['totalcommision'])."</td>
                            <td valign='top' align='left' ".$style.">".Mage::app()->getStore()->formatPrice($row['actualparterprocost'])."</td>
                         </tr>";
        }
        if($actparterprocost){
            $collectionverifyread = Mage::getModel('vendor/saleperpartner')->getCollection();
            $collectionverifyread->addFieldToFilter('mageuserid',array('eq'=>$vendor_id));
            if(count($collectionverifyread)>=1){
                foreach($collectionverifyread as $verifyrow){
                    if($verifyrow->getAmountremain() >= $actparterprocost){
                        $totalremain=$verifyrow->getAmountremain()-$actparterprocost;
                    }
                    else{
                        $totalremain=0;
                    }
                    $verifyrow->setAmountremain($totalremain);
                    $verifyrow->save();
                    $totalremain;
                    $amountpaid=$verifyrow->getAmountrecived();
                    $totalrecived=$actparterprocost+$amountpaid;
                    $verifyrow->setAmountpaid($actparterprocost);
                    $verifyrow->setAmountrecived($totalrecived);
                    $verifyrow->setAmountremain($totalremain);
                    $verifyrow->save();
                }
            }
            else{
                $percent = Mage::getStoreConfig('vendor/vendor_options/percent');
                $collectionf=Mage::getModel('vendor/saleperpartner');
                $collectionf->setMageuserid($vendor_id);
                $collectionf->setTotalsale($totalamount);
                $collectionf->setAmountpaid($actparterprocost);
                $collectionf->setAmountrecived($actparterprocost);
                $collectionf->setAmountremain(0);
                $collectionf->setCommision($percent);
                $collectionf->save();
            }

            $unique_id = $this->checktransid();
            if($unique_id!=''){
                $vendor_trans = Mage::getModel('vendor/transaction')->getCollection()
                        ->addFieldToFilter('transactionid',array('eq'=>mysqli_real_escape_string($unique_id)));
                if(count($vendor_trans)){
                    foreach ($vendor_trans as $value) {
                        $id =$value->getId();
                        if($id){
                            Mage::getModel('vendor/transaction')->load($id)->delete();
                        }
                    }
                }
                $currdate = date('Y-m-d H:i:s');
                $vendor_trans = Mage::getModel('vendor/transaction');
                $vendor_trans->setTransactionid($unique_id);
                $vendor_trans->setTransactionamount($actparterprocost);
                $vendor_trans->setType('Manual');
                $vendor_trans->setMethod('Manual');
                $vendor_trans->setVendorid($vendor_id);
                $vendor_trans->setCustomnote($wholedata['vendor_pay_reason']);
                $vendor_trans->setCreatedAt($currdate);
                $transid = $vendor_trans->save()->getTransid();
            }




            $vendor = Mage::getModel('customer/customer')->load($vendor_id);
            $emailTemp = Mage::getModel('core/email_template')->loadDefault('transactionmail');

            $emailTempVariables = array();
            $admin_storemail = Mage::getStoreConfig('vendor/vendor_options/adminemail');
            $adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
            $adminUsername = 'Admin';
            $emailTempVariables['myvar1'] = $vendor->getName();
            $emailTempVariables['myvar2'] = $transid;
            $emailTempVariables['myvar3'] = $currdate;
            $emailTempVariables['myvar4'] = $actparterprocost;
            $emailTempVariables['myvar5'] = $orderinfo;
            $emailTempVariables['myvar6'] = $wholedata['vendor_pay_reason'];
            $processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
            $emailTemp->setSenderName($adminUsername);
            $emailTemp->setSenderEmail($adminEmail);
            $emailTemp->send($vendor->getEmail(),$vendor->getName(),$emailTempVariables);

            $this->_getSession()->addSuccess(Mage::helper('vendor')->__('Payment has been successfully done for this vendor'));
        }
        $this->_redirect('vendor/adminhtml_order/index',array('id'=>$vendor_id));
    }

    public function randString($length, $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')
    {
        $str = 'tr-';
        $count = strlen($charset);
        while ($length--) {
            $str .= $charset[mt_rand(0, $count-1)];
        }

        return $str;
    }

    public function checktransid(){
        $unique_id=$this->randString(11);
        $collection = Mage::getModel('vendor/transaction')->getCollection()
                    ->addFieldToFilter('transactionid',array('eq'=>($unique_id)));
        $i=0;
        foreach ($collection as $value) {
                $i++;
        }
        if($i!=0){
            $this->checktransid();
        }else{
            return $unique_id;
        }
    }
}