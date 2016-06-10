<?php
require_once('CreateController.php');
class ITwebexperts_Request4quote_Adminhtml_Quote_EditController extends ITwebexperts_Request4quote_Adminhtml_Quote_CreateController {

    protected function _isAllowed(){
        return Mage::getSingleton('admin/session')->isAllowed('admin/request4quote/quotes');
    }

    public function startAction()
    {
        $this->_getSession()->clear();
        $quoteId = $this->getRequest()->getParam('quote_id');
        $quote = Mage::getModel('request4quote/quote')->load($quoteId);
        if ($quoteId) {
            $quote->loadByIdWithoutStore($quoteId);

        }
        $this->_getOrderCreateModel()->initFromQuote($quote);
		$this->_redirect('*/quote_edit/index');
    }
	
	public function indexAction()
	{
		$this->_title($this->__('Request4Quote'))->_title($this->__('Quotes'))->_title($this->__('Edit Quote'));
        $this->_initSession();
        $this->loadLayout();
        $this->_setActiveMenu('request4quote/quote')
            ->renderLayout();
	}

    /**
     * Custom options downloader
     *
     * @param mixed $info
     */
    protected function _downloadFileAction($info)
    {

        try {

            $filePath = Mage::getBaseDir() . $info['order_path'];
            if ((!is_file($filePath) || !is_readable($filePath)) && !$this->_processDatabaseFile($filePath)) {
                //try get file from quote
                $filePath = Mage::getBaseDir() . $info['quote_path'];
                if ((!is_file($filePath) || !is_readable($filePath)) && !$this->_processDatabaseFile($filePath)) {
                    throw new Exception();
                }
            }
            $this->_prepareDownloadResponse($info['title'], array(
                'value' => $filePath,
                'type'  => 'filename'
            ));
        } catch (Exception $e) {
            $this->_forward('noRoute');
        }
    }

    /**
     * Check file in database storage if needed and place it on file system
     *
     * @param string $filePath
     * @return bool
     */
    protected function _processDatabaseFile($filePath)
    {
        if (!Mage::helper('core/file_storage_database')->checkDbUsage()) {
            return false;
        }

        $relativePath = Mage::helper('core/file_storage_database')->getMediaRelativePath($filePath);
        $file = Mage::getModel('core/file_storage_database')->loadByFilename($relativePath);

        if (!$file->getId()) {
            return false;
        }

        $directory = dirname($filePath);
        @mkdir($directory, 0777, true);

        $io = new Varien_Io_File();
        $io->cd($directory);

        $io->streamOpen($filePath);
        $io->streamLock(true);
        $io->streamWrite($file->getContent());
        $io->streamUnlock();
        $io->streamClose();

        return true;
    }

    /**
     * Profile custom options download action
     */
    public function downloadProfileCustomOptionAction()
    {
        $recurringProfile = Mage::getModel('sales/recurring_profile')->load($this->getRequest()->getParam('id'));

        if (!$recurringProfile->getId()) {
            $this->_forward('noRoute');
        }

        $orderItemInfo = $recurringProfile->getData('order_item_info');
        try {
            $request = unserialize($orderItemInfo['info_buyRequest']);

            if ($request['product'] != $orderItemInfo['product_id']) {
                $this->_forward('noRoute');
                return;
            }

            $optionId = $this->getRequest()->getParam('option_id');
            if (!isset($request['options'][$optionId])) {
                $this->_forward('noRoute');
                return;
            }
            // Check if the product exists
            $product = Mage::getModel('catalog/product')->load($request['product']);
            if (!$product || !$product->getId()) {
                $this->_forward('noRoute');
                return;
            }
            // Try to load the option
            $option = $product->getOptionById($optionId);
            if (!$option || !$option->getId() || $option->getType() != 'file') {
                $this->_forward('noRoute');
                return;
            }
            $this->_downloadFileAction($request['options'][$this->getRequest()->getParam('option_id')]);
        } catch (Exception $e) {
            $this->_forward('noRoute');
        }
    }

    /**
     * Custom options download action
     */
    public function downloadCustomOptionAction()
    {

        $info = $this->getRequest()->getParams();
        foreach($info as $key => $param){
            $info[$key] = Mage::helper('core')->urlDecode($param);
        }
        if (empty($info)) {
            $info = new Varien_Object();
        } else if (is_array($info)) {
            $info = new Varien_Object($info);
        }

        try {
            $this->_downloadFileAction($info);
        } catch (Exception $e) {
            $this->_forward('noRoute');
        }
        exit(0);
    }

}