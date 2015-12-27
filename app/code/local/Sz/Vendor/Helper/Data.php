<?php

class Sz_Vendor_Helper_Data extends Mage_Core_Helper_Abstract
{
    const IMPORTED_PRODUCT_CSV_FILE_DIRECTORY = 'import/Vendor';
    const IMPORTED_PRODUCT_ZIP_FILE_DIRECTORY = 'import/Vendor_Image';
    const ARCHIVE_PRODUCT_CSV_FILE_DIRECTORY = 'import/archive/Vendor';
    const ARCHIVE_PRODUCT_ZIP_FILE_DIRECTORY = 'import/archive/Vendor_Image';
    const MOVE_PRODUCT_TO_MEDIA_DIRECTORY = 'catalog/product/';
    const SAMPLE_FILE_PATH = 'sample/vendor/sample.csv';

    /**
     *
     * Get the absolute path of the exported order file directory from configuration
     */
    public function getProductImportedFileDirectory()
    {
        $path = Mage::getBaseDir('var').DS.self::IMPORTED_PRODUCT_CSV_FILE_DIRECTORY.DS;
        $io = new Varien_Io_File();
        $io->checkAndCreateFolder($path);
        return $path;
    }

    /**
     *
     * Get the absolute path of the exported order file directory from configuration
     */
    public function getProductImageDirectory()
    {
        $path = Mage::getBaseDir('media').DS.self::MOVE_PRODUCT_TO_MEDIA_DIRECTORY.DS;
        $io = new Varien_Io_File();
        $io->checkAndCreateFolder($path);
        return $path;
    }
    public function getProductImportedImageFileDirectory()
    {
        $path = Mage::getBaseDir('var').DS.self::IMPORTED_PRODUCT_ZIP_FILE_DIRECTORY.DS;
        $io = new Varien_Io_File();
        $io->checkAndCreateFolder($path);
        return $path;
    }

    public function getArchiveImageFileDirectory()
    {
        $path = Mage::getBaseDir('var').DS.self::ARCHIVE_PRODUCT_ZIP_FILE_DIRECTORY.DS;
        $io = new Varien_Io_File();
        $io->checkAndCreateFolder($path);
        return $path;
    }
    /**
     *
     * Get the absolute path of the archieve feed directory from configuration
     */
    public function getArchiveDirectory()
    {
        $path = Mage::getBaseDir('var').DS.self::ARCHIVE_PRODUCT_CSV_FILE_DIRECTORY.DS;
        $io = new Varien_Io_File();
        $io->checkAndCreateFolder($path);
        return $path;
    }

    public function getSampleFilePath() {
        return Mage::getBaseDir('var').DS.self::SAMPLE_FILE_PATH;
    }
}