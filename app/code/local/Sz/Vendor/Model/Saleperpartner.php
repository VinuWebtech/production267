<?php

class Sz_Vendor_Model_Saleperpartner extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('vendor/saleperpartner');
    }

    public function loadByVendorId($vendorId = null) {
        $collection = $this->getResourceCollection()
            ->addFieldToFilter('mageuserid',array('eq'=>$vendorId))
            ->setCurPage(1)
            ->setPageSize(1);
        foreach ($collection as $object) {
            return $object;
        }
        return false;
    }
	
	public function salePayment($data)
	{
		$wholedata=$data->getParams('');
		$verifyrow = Mage::getModel('vendor/saleperpartner')->load($wholedata['ID']);
		
			if($verifyrow->getAmountremain()>0){
			  $lastpayment = $verifyrow->getAmountremain();
				$collectionsave=Mage::getModel('vendor/saleslist');
				$collectionsave->setMageproid(0);
				$collectionsave->setMageorderid(0);
				$collectionsave->setMagerealorderid(0);
				$collectionsave->setMagequantity(0);
				$collectionsave->setMageproownerid($verifyrow->getmageuserid());
				$collectionsave->setCpprostatus(1);
				$collectionsave->setTotalamount(0);
				$collectionsave->setMagebuyerid(0);
				$collectionsave->setMageproprice(0);
				$collectionsave->setMageproname('manual');
	            $collectionsave->setTotalcommision(0);
				$collectionsave->setActualparterprocost(-$verifyrow->getamountremain());
				$collectionsave->setClearedAt(date('Y-m-d H:i:s'));
				$collectionsave->save(); 		
			    $amountpaid=$verifyrow->getAmountrecived();
			    $totalrecived=$verifyrow->getAmountremain()+$amountpaid;
				$verifyrow->setAmountpaid($lastpayment);
				$verifyrow->setAmountrecived($totalrecived);
				$verifyrow->setAmountremain(0);
				$verifyrow->save();
				return 0;
			}	
	}

	public function masssalePayment($data)
	{
		$wholedata=$data->getParams();
		foreach ($wholedata['customer'] as $id) {
			$verifyrow = Mage::getModel('vendor/saleperpartner')->load($id);
			if($verifyrow->getAmountremain()>0){
				$collectionsave=Mage::getModel('vendor/saleslist');
				$collectionsave->setMageproid(0);
				$collectionsave->setMageorderid(0);
				$collectionsave->setMagerealorderid(0);
				$collectionsave->setMagequantity(0);
				$collectionsave->setMageproownerid($verifyrow->getmageuserid());
				$collectionsave->setCpprostatus(1);
				$collectionsave->setTotalamount(0);
				$collectionsave->setMagebuyerid(0);
				$collectionsave->setMageproprice(0);
				$collectionsave->setMageproname('manual');
				$collectionsave->setTotalcommision(0);
				$collectionsave->setActualparterprocost(-$verifyrow->getamountremain());
				$collectionsave->setClearedAt(date('Y-m-d H:i:s'));
				$collectionsave->save();

				$amountpaid=$verifyrow->getAmountrecived();
				$lastpayment=$verifyrow->getAmountremain();
				$totalrecived=$lastpayment+$amountpaid;
				$verifyrow->setAmountpaid($lastpayment);
				$verifyrow->setAmountrecived($totalrecived);
				$verifyrow->setAmountremain(0);
				$verifyrow->save();
			}
		}
		return 0;	
	}

}
