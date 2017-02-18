<?php
class Buildmatic_Brands_Model_Observer extends Varien_Event_Observer
{
    public function addBrandSection($observer)
    {
        $sitemapObject = $observer->getSitemapObject();
        if (!($sitemapObject instanceof Mage_Sitemap_Model_Sitemap)) {
            throw new Exception(Mage::helper('buildmatic_brands')->__('Error during generation sitemap'));
        }

        $storeId = $sitemapObject->getStoreId();
        //$date = Mage::getSingleton('core/date')->gmtDate('Y-m-d');
        $baseUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
        /**
         * Generate blog pages sitemap
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/page/changefreq');
        $priority = (string)Mage::getStoreConfig('sitemap/page/priority');

        $moduleName = 'Buildmatic_Brands';
        
        //if(Mage::getConfig()->getModuleConfig($moduleName)->is('active', 'true'))
        if(Mage::helper('core')->isModuleOutputEnabled('Buildmatic_Brands'))
        {
            //$collection = Mage::getModel('buildmatic_brands/brands')->getCollection()->addStoreFilter($storeId);
            $collection = Mage::getModel('buildmatic_brands/brands')->getCollection();
            $route = Mage::getStoreConfig('brands_section/brands_settings/url_prefix');
            foreach ($collection as $item) {
                $xml = sprintf(
                    '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                    htmlspecialchars($baseUrl . $route . '/' . $item->getUrlKey()), substr(Mage::getModel('buildmatic_brands/brands')->load($item->getId())->getUpdatedAt(),0,10), $changefreq, $priority
                    //htmlspecialchars($baseUrl . $item->getUrlKey()), substr(Mage::getModel('buildmatic_brands/brands')->load($item->getId())->getUpdatedAt(),0,10), $changefreq, $priority
                );

                $sitemapObject->sitemapFileAddLine($xml);
            }
            unset($collection);
        }
    }
}