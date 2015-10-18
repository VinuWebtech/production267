<?php

$installer = $this;
$installer->startSetup();

$installer->setCustomerAttributes(
    array(
        'sz_ajaxlogin_tid' => array(
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false                
        ),            
        'sz_ajaxlogin_ttoken' => array(
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false                
        )          
    )
);

// Install our custom attributes
$installer->installCustomerAttributes();

// Remove our custom attributes (for testing)
//$installer->removeCustomerAttributes();

$installer->endSetup();