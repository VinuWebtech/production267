<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/** Update quotes with new order status to NULL because new used to not be shown on the quote grid but we want
 * it to be */

$quoteCollection = Mage::getModel('request4quote/quote')->getCollection();
foreach($quoteCollection as $quote)
{
    if($quote->getR4qStatus() == 'new'){
        $quote->setR4qStatus(NULL);
        $quote->save();
    }
}

$installer->endSetup();
