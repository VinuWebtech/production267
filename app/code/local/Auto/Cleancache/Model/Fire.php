<?php
class Auto_Cleancache_Model_Fire
{
    public function fireCacheafterCronrun(){

     /**
     * Flush all magento cache
     */
     Mage::app()->cleanCache();
    }
}
