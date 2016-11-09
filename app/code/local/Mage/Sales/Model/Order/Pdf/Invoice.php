<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright  Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales Order Invoice PDF model
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Order_Pdf_Invoice extends Mage_Sales_Model_Order_Pdf_Abstract
{
    /**
     * Draw header for page
     *
     * @param Zend_Pdf_Page $page
     * @return void
     */
    protected function insertHeaderText(Zend_Pdf_Page $page)
    {
        /* Add page header */
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $font = $this->_setFontBold($page, 12);
        $page->drawText("Retail/Tax Invoice/Cash Memorandum", 25, 800, 'UTF-8');
    }

    /**
     * Draw Line for page
     *
     * @param Zend_Pdf_Page $page
     * @return void
     */
    protected function _drawLine(Zend_Pdf_Page $page)
    {
        //$this->_setFontRegular($page, 9);
        //$page->setLineColor(new Zend_Pdf_Color_GrayScale(0));
        $page->setLineWidth(1);
        $page->drawLine(25, $this->y - 15, 570, $this->y - 15);
    }

    /**
     * Draw header for item table
     *
     * @param Zend_Pdf_Page $page
     * @return void
     */
    protected function _drawHeader(Zend_Pdf_Page $page)
    {
        /* Add table head */
        $this->_drawLine($page);
        $this->_setFontRegular($page, 9);
        $this->y -= 10;

        //columns headers
        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('QTY'),
            'feed'  => 35,
            'align' => 'left'
        );

        $lines[0][] = array(
            'text' => Mage::helper('sales')->__('ITEM DESCRIPTION'),
            'feed' => 65,
            'align' => 'left'
        );

        /*$lines[0][] = array(
            'text'  => Mage::helper('sales')->__('SKU'),
            'feed'  => 320,
            'align' => 'left'
        );*/

        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('PRICE'),
            'feed'  => 285,
            'align' => 'left'
        );

        /*$lines[0][] = array(
            'text'  => Mage::helper('sales')->__('DISCOUNT'),
            'feed'  => 325,
            'align' => 'left'
        );*/

        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('SUBTOTAL'),
            'feed'  => 390,
            'align' => 'left'
        );

        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('TAX RATE'),
            'feed'  => 465,
            'align' => 'left'
        );

        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('TAX AMT'),
            'feed'  => 515,
            'align' => 'left'
        );

        $lineBlock = array(
            'lines'  => $lines,
            'height' => 5
        );

        $this->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->y -= 10;
    }

    /**
     * Return PDF document
     *
     * @param  array $invoices
     * @return Zend_Pdf
     */
    public function getPdf($invoices = array())
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($invoices as $invoice) {
            if ($invoice->getStoreId()) {
                Mage::app()->getLocale()->emulate($invoice->getStoreId());
                Mage::app()->setCurrentStore($invoice->getStoreId());
            }
            $page  = $this->newPage();
            $order = $invoice->getOrder();
            /* Add Header Text */
            $this->insertHeaderText($page);
            /* Add address */
            $this->insertAddress($page, $invoice->getStore());
            /* Add head */
            $this->insertOrder(
                $page,
                $order,
                Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID, $order->getStoreId())
            );
            /* Add document text and number */
            $this->insertDocumentNumber(
                $page,
                Mage::helper('sales')->__('Invoice Number: ') . $invoice->getIncrementId()
            );
            /* Add table */
            $this->_drawHeader($page);
            /* Add body */
            foreach ($invoice->getAllItems() as $item){
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                /* Draw item */
                $this->_drawItem($item, $page, $order);
                $page = end($pdf->pages);
            }
            $this->_drawLine($page);
            /* Add totals */
            $this->insertTotals($page, $invoice);
            if ($invoice->getStoreId()) {
                Mage::app()->getLocale()->revert();
            }
            $this->_drawLine($page);
            $this->y -= 10;
            //$page->drawText("I / We certify that my / our registration certificate under the Maharastra Value Added Tax Act, 2002 is in force on the date on which the sale of the goods specified in this tax invoice is made by me / us and that the transaction of sale covered by this tax invoice has been effected by me / us and it shall be accounted for in the turnover of sales while filing of return and the due tax, if any, payable on th sale has been paid or shall be paid.", 35, $this->y - 25, 'UTF-8');

            /* Add For stamp sign */
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $font = $this->_setFontBold($page, 9);
            $page->drawText("For Vedhaansh Engineering Pvt Ltd.", 385, $this->y - 20, 'UTF-8');

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
            $page->drawRectangle(385, $this->y - 25, 560, $this->y - 75);

            $image = Mage::getBaseDir('media') . '/Invoice-Sign.png';
            if (is_file($image)) {
                $image       = Zend_Pdf_Image::imageWithPath($image);
                $top         = $this->y - 28; //bottom border of the page
                $widthLimit  = 120; //half of the page width
                $heightLimit = 25; //assuming the image is not a "skyscraper"
                $width       = $image->getPixelWidth();
                $height      = $image->getPixelHeight();

                //preserving aspect ratio (proportions)
                $ratio = $width / $height;
                if ($ratio > 1 && $width > $widthLimit) {
                    $width  = $widthLimit;
                    $height = $width / $ratio;
                } elseif ($ratio < 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width  = $height * $ratio;
                } elseif ($ratio == 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width  = $widthLimit;
                }

                $y1 = $top - $height;
                $y2 = $top;
                $x1 = 400;
                $x2 = $x1 + $width;

                //coordinates after transformation are rounded by Zend
                $page->drawImage($image, $x1, $y1, $x2, $y2);

                $this->y = $y1 - 10;
            }

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.5));
            $font = $this->_setFontBold($page, 9);
            $page->drawText("(Authorised Signatory)", 415, $this->y - 5, 'UTF-8');
            
            $page->setLineWidth(0.5);
            $page->drawLine(25, $this->y - 15, 570, $this->y - 15);
            $this->y -= 10;
            //$this->_drawLine($page);
            /* Add image */
            $this->insertLogo($page, $invoice->getStore());
        }
        $this->_afterGetPdf();
        return $pdf;
    }

    /**
     * Create new page and assign to PDF object
     *
     * @param  array $settings
     * @return Zend_Pdf_Page
     */
    public function newPage(array $settings = array())
    {
        /* Add new table head */
        $page = $this->_getPdf()->newPage(Zend_Pdf_Page::SIZE_A4);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;
        if (!empty($settings['table_header'])) {
            $this->_drawHeader($page);
        }
        return $page;
    }
}
