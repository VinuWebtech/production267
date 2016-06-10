<?php

$statusUpdate = Mage::getModel('request4quote/quote_status')->getCollection()->addFieldToFilter('status','accepted')->getFirstItem();
$statusUpdate->setAllowviewcheckout(1);
$statusUpdate->save();

$statusUpdate = Mage::getModel('request4quote/quote_status')->getCollection()->addFieldToFilter('status','declined')->getFirstItem();
$statusUpdate->setAllowviewcheckout(0);
$statusUpdate->save();

$statusUpdate = Mage::getModel('request4quote/quote_status')->getCollection()->addFieldToFilter('status','new')->getFirstItem();
$statusUpdate->setAllowviewcheckout(0);
$statusUpdate->save();

$statusUpdate = Mage::getModel('request4quote/quote_status')->getCollection()->addFieldToFilter('status','ordered')->getFirstItem();
$statusUpdate->setAllowviewcheckout(1);
$statusUpdate->save();


$statusUpdate = Mage::getModel('request4quote/quote_status')->getCollection()->addFieldToFilter('status','processed')->getFirstItem();
$statusUpdate->setAllowviewcheckout(1);
$statusUpdate->save();

$statusUpdate = Mage::getModel('request4quote/quote_status')->getCollection()->addFieldToFilter('status','processing')->getFirstItem();
$statusUpdate->setAllowviewcheckout(0);
$statusUpdate->save();

$statusUpdate = Mage::getModel('request4quote/quote_status')->getCollection()->addFieldToFilter('status','rejected')->getFirstItem();
$statusUpdate->setAllowviewcheckout(1);
$statusUpdate->save();
