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
class ITwebexperts_Request4quote_Block_Adminhtml_Quote_Create_Search_Grid_Renderer_Qty extends
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
		// Prepare values
		$isInactive = $this->_isInactive($row);

		if ($isInactive) {
			$qty = '';
		} else {
			$qty = $row->getData($this->getColumn()->getIndex());
			$qty *= 1;
			if (!$qty) {
				$qty = '';
			}
		}

		// Compose html
		$html = '<input type="text" ';
		$html .= 'name="' . $this->getColumn()->getId() . '" ';
		$html .= 'value="' . $qty . '" ';
		$Product = Mage::getModel('catalog/product')->load($row->getData('entity_id'));
		/*if ($row->getTypeId() == ITwebexperts_Payperrentals_Helper_Data::PRODUCT_TYPE || $Product->getIsReservation() != ITwebexperts_Payperrentals_Model_Product_Isreservation::STATUS_DISABLED) {
			$html .= 'readonly="readonly'.$this->getColumn()->getIndex().'" ';
		} */
		if ($isInactive) {
			$html .= 'disabled="disabled" ';
		}
		$html .= 'class="input-text ' . $this->getColumn()->getInlineCss() . ($isInactive ? ' input-inactive' : '') . '" />';
		return $html;
	}

}
