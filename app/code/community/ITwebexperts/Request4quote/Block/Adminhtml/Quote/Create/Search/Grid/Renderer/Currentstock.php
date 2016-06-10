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
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales create order product search grid price column renderer
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class ITwebexperts_Request4quote_Block_Adminhtml_Quote_Create_Search_Grid_Renderer_Currentstock extends
	Mage_Adminhtml_Block_Sales_Order_Create_Search_Grid_Renderer_Qty
{
    /**
     * Render minimal price for downloadable products
     *
     * @param   Varien_Object $row
     * @return  string
     */
	public function render(Varien_Object $row)
	{
        $_product = Mage::getModel('catalog/product')->load($row->getData($this->getColumn()->getIndex()));
        if(Mage::getSingleton('core/session')->getData('dropWarehousesInitial')){
            $stockId = Mage::getSingleton('core/session')->getData('dropWarehousesInitial');
        }else{
            $stockId = '1';
        }

        $warehouseEnabled = $this->_getModuleHelper()->hasWarehouse();
        $rentalEnabled = $this->_getModuleHelper()->isRentalInstalled();

        if ($warehouseEnabled){
            $qtyStock = Mage::helper('pprwarehouse')->getQtyForProductAndStock($_product, $stockId);
        } else {
            $qtyStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product)->getQty();
        }

        if ($rentalEnabled) {
            if ($_product->getTypeId() == ITwebexperts_Payperrentals_Helper_Data::PRODUCT_TYPE || ($_product->getTypeId() == ITwebexperts_Payperrentals_Helper_Data::PRODUCT_TYPE_CONFIGURABLE && $_product->getIsReservation() != ITwebexperts_Payperrentals_Model_Product_Isreservation::STATUS_DISABLED) || ($_product->getTypeId() == ITwebexperts_Payperrentals_Helper_Data::PRODUCT_TYPE_GROUPED && $_product->getIsReservation() != ITwebexperts_Payperrentals_Model_Product_Isreservation::STATUS_DISABLED) || ($_product->getTypeId() == ITwebexperts_Payperrentals_Helper_Data::PRODUCT_TYPE_BUNDLE && $_product->getIsReservation() != ITwebexperts_Payperrentals_Model_Product_Isreservation::STATUS_DISABLED)) {
                //get price for selected dates.
                if (Mage::getSingleton('core/session')->getData('startDateInitial')) {
                    $startDate = Mage::getSingleton('core/session')->getData('startDateInitial');
                } else {
                    $startDate = date('Y-m-d H:i:s');
                }
                if (Mage::getSingleton('core/session')->getData('endDateInitial')) {
                    $endDate = Mage::getSingleton('core/session')->getData('endDateInitial');
                } else {
                    $endDate = date('Y-m-d H:i:s');
                }
            }
        }

        if ($rentalEnabled && $warehouseEnabled) {
            $html = '<span class="currentstock">' . ITwebexperts_PPRWarehouse_Helper_Payperrentals_Data::getAvailability($_product->getId(), 1, $startDate, $endDate, $stockId) . '</span>';
        } else {
            $html = '<span class="currentstock">' . $qtyStock . '</span>';
        }
        return $html;
	}

    /**
     * Returns default module helper.
     *
     * @return ITwebexperts_Request4quote_Helper_Data
     */
    public function _getModuleHelper()
    {
        return Mage::helper('request4quote');
    }

}
