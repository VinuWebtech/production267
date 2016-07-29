<?php

class Smartwave_Blog_Model_Sitemap extends Mage_Sitemap_Model_Sitemap
{
    protected $_io;

    public function generateXml()
    {
        if (Mage::helper('blog')->extensionEnabled('Smartwave_Ascurl')) {
            return Mage::getModel('ascurl/sitemap')->setData($this->getData())->generateXml();
        }

        $this->fileCreate();

        $storeId = $this->getStoreId();
        $date = Mage::getSingleton('core/date')->gmtDate('Y-m-d');
        $baseUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);

        /**
         * Generate categories sitemap
         */
        /*$changefreq = (string)Mage::getStoreConfig('sitemap/category/changefreq');
        $priority = (string)Mage::getStoreConfig('sitemap/category/priority');
        $collection = Mage::getResourceModel('sitemap/catalog_category')->getCollection($storeId);
        foreach ($collection as $item) {
            $xml = sprintf(
                '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()), $date, $changefreq, $priority
            );
            $this->sitemapFileAddLine($xml);
        }
        unset($collection);

        /**
         * Generate products sitemap
         */
        /*$changefreq = (string)Mage::getStoreConfig('sitemap/product/changefreq');
        $priority = (string)Mage::getStoreConfig('sitemap/product/priority');
        $collection = Mage::getResourceModel('sitemap/catalog_product')->getCollection($storeId);
        foreach ($collection as $item) {
            $xml = sprintf(
                '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()), $date, $changefreq, $priority
            );
            $this->sitemapFileAddLine($xml);
        }
        unset($collection);*/

        $mediaUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA); //media link path for case of using cdn path if using for images

        /**
         * Generate categories sitemap
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/category/changefreq');
        $priority = (string)Mage::getStoreConfig('sitemap/category/priority');
        $collection = Mage::getResourceModel('sitemap/catalog_category')->getCollection($storeId);
        foreach ($collection as $item) {
            $img = Mage::getModel('catalog/category')->load($item->getId())->getImageUrl();
            if ($img)//only add image info to the url if images exist
            {
                $xml = sprintf(
                    '<url><loc>%s</loc><image:image><image:loc>%s</image:loc></image:image><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                    htmlspecialchars($baseUrl . $item->getUrl()), $img, substr(Mage::getModel('catalog/category')->load($item->getId())->getUpdated_at(),0,10), $changefreq, $priority
                );
            }else{
                $xml = sprintf(
                    '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                    htmlspecialchars($baseUrl . $item->getUrl()), substr(Mage::getModel('catalog/category')->load($item->getId())->getUpdated_at(),0,10), $changefreq, $priority
                );
            }
            $this->sitemapFileAddLine($xml);
        }
        unset($collection);

        /**
         * Generate products sitemap
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/product/changefreq');
        $priority = (string)Mage::getStoreConfig('sitemap/product/priority');
        $collection = Mage::getResourceModel('sitemap/catalog_product')->getCollection($storeId);
        foreach ($collection as $item) {
            $image = Mage::getModel('catalog/product')->load($item->getId())->image;
            if ($image != "no_selection") { //if base image exists for the product
                $xml = sprintf(
                    '<url><loc>%s</loc><image:image><image:loc>%s</image:loc></image:image><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                    htmlspecialchars($baseUrl . $item->getUrl()), htmlspecialchars($mediaUrl . "catalog/product" . $image), substr(Mage::getModel('catalog/product')->load($item->getId())->getUpdated_at(),0,10), $changefreq, $priority
                );
            }else{
                $xml = sprintf(
                    '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                    htmlspecialchars($baseUrl . $item->getUrl()), substr(Mage::getModel('catalog/product')->load($item->getId())->getUpdated_at(),0,10), $changefreq, $priority
                );
            }
            $this->sitemapFileAddLine($xml);
        }
        unset($collection);

        /**
         * Generate cms pages sitemap
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/page/changefreq');
        $priority = (string)Mage::getStoreConfig('sitemap/page/priority');
        $collection = Mage::getResourceModel('sitemap/cms_page')->getCollection($storeId);
        foreach ($collection as $item) {
            $xml = sprintf(
                '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()), $date, $changefreq, $priority
            );
            $this->sitemapFileAddLine($xml);
        }
        unset($collection);

        Mage::dispatchEvent('sitemap_add_xml_block_to_the_end', array('sitemap_object' => $this));

        $this->fileClose();

        $this->setSitemapTime(Mage::getSingleton('core/date')->gmtDate('Y-m-d H:i:s'));
        $this->save();

        return $this;
    }

    protected function fileCreate()
    {
        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $this->getPath()));
        $io->streamOpen($this->getSitemapFilename());

        $io->streamWrite('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
        //$io->streamWrite('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
        $io->streamWrite('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">');
        $this->_io = $io;
    }

    protected function fileClose()
    {
        $this->_io->streamWrite('</urlset>');
        $this->_io->streamClose();
    }

    public function sitemapFileAddLine($xml)
    {
        $this->_io->streamWrite($xml);
    }
}