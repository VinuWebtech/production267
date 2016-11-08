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
 * Sales Order Invoice Pdf default items renderer
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Order_Pdf_Items_Invoice_Default extends Mage_Sales_Model_Order_Pdf_Items_Abstract
{
    /**
     * Draw item line
     */
    public function draw()
    {
        $order  = $this->getOrder();
        $item   = $this->getItem();
        $pdf    = $this->getPdf();
        $page   = $this->getPage();
        $lines  = array();

        // draw QTY
        $lines[0] = array(array(
            'text'  => $item->getQty() * 1,
            'feed'  => 35,
            'align' => 'left'
        ));

        // draw Product name & sku
        $sku = $page->drawText($this->getSku($item), 305, $this->y - 20, 'UTF-8');
        $lines[0][] = array(
            'text' => Mage::helper('core/string')->str_split($item->getName()."(".$this->getSku($item).")", 35, true, true),
            'feed' => 65,
            'align' => 'left'
        );

        // draw SKU
        /*$lines[0][] = array(
            'text'  => Mage::helper('core/string')->str_split($this->getSku($item), 17),
            'feed'  => 320,
            'align' => 'left'
        );*/

        /*$discAmt = $item->getDiscountAmount();
        $price = $item->getPrice() * $item->getQty();
        $origprice = round($price + $discAmt);

        // draw Original Price
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($price),
            'feed'  => 255,
            //'font'  => 'bold',
            'align' => 'left'
        );

        // draw Discount Amount
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($discAmt),
            'feed'  => 325,
            //'font'  => 'bold',
            'align' => 'left'
        );

        // draw Subtotal
        $subTotal = $price;
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($subTotal),
            'feed'  => 390,
            //'font'  => 'bold',
            'align' => 'left'
        );*/

        // draw item Prices
        $i = 0;
        $prices = $this->getItemPricesForDisplay();
        $feedPrice = 285;
        $feedSubtotal = $feedPrice + 105;
        foreach ($prices as $priceData){
            if (isset($priceData['label'])) {
                // draw Price label
                $lines[$i][] = array(
                    'text'  => $priceData['label'],
                    'feed'  => $feedPrice,
                    'align' => 'left'
                );
                // draw Subtotal label
                $lines[$i][] = array(
                    'text'  => $priceData['label'],
                    'feed'  => $feedSubtotal,
                    'align' => 'left'
                );
                $i++;
            }
            // draw Price
            $lines[$i][] = array(
                'text'  => $priceData['price'],
                'feed'  => $feedPrice,
                'font'  => 'bold',
                'align' => 'left'
            );
            // draw Subtotal
            $lines[$i][] = array(
                'text'  => $priceData['subtotal'],
                'feed'  => $feedSubtotal,
                'font'  => 'bold',
                'align' => 'left'
            );
            $i++;
        }

        // draw Tax Rate
        $taxPercent = $item->getOrderItem()->getTaxPercent();
        $taxPercent = number_format($taxPercent,2,'.','') . '%';
        $lines[0][] = array(
            'text'  => $taxPercent,
            'feed'  => 465,
            //'font'  => 'bold',
            'align' => 'left'
        );

        // draw Tax
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getTaxAmount()),
            'feed'  => 515,
            'font'  => 'bold',
            'align' => 'left'
        );

        // custom options
        $options = $this->getItemOptions();
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $lines[][] = array(
                    'text' => Mage::helper('core/string')->str_split(strip_tags($option['label']), 40, true, true),
                    'font' => 'italic',
                    'feed' => 65
                );

                if ($option['value']) {
                    if (isset($option['print_value'])) {
                        $_printValue = $option['print_value'];
                    } else {
                        $_printValue = strip_tags($option['value']);
                    }
                    $values = explode(', ', $_printValue);
                    foreach ($values as $value) {
                        $lines[][] = array(
                            'text' => Mage::helper('core/string')->str_split($value, 30, true, true),
                            'feed' => 70
                        );
                    }
                }
            }
        }

        $lineBlock = array(
            'lines'  => $lines,
            'height' => 10
        );

        $page = $pdf->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $this->setPage($page);
    }
}
