<?php
class ITwebexperts_Request4quote_Block_Cart_Totals extends Mage_Checkout_Block_Cart_Totals
{
    const MODE_TOTALS_SHOW = 'show';
    const MODE_TOTALS_HIDE = 'hide';

    /**
     * Contains information about allowed totals.
     * @var null|array
     */
    protected $allowedTotals = null;

    /**
     * Show block (yes/no)
     * @var bool
     */
    protected $showBlock = false;

    /**
     * Get active or custom quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if ($this->getCustomQuote()) {
            return $this->getCustomQuote();
        }

        if (null === $this->_quote) {
            $this->_quote = $this->_getCartHelper()->getQuote();
        }
        return $this->_quote;
    }

    /**
     * Returns cart helper.
     *
     * @return ITwebexperts_Request4quote_Helper_Cart
     */
    protected function _getCartHelper()
    {
        return $this->helper('request4quote/cart');
    }

    /**
     * Returns default helper.
     *
     * @return ITwebexperts_Request4quote_Helper_Data
     */
    protected function _getModuleHelper()
    {
        return $this->helper('request4quote');
    }

    /**
     * Returns allowed totals (to display). Basically we want
     * to hide everything except taxes & shipping.
     *
     * @return array
     */
    public function getAllowedTotals()
    {
        if (is_null($this->allowedTotals)) {
            $this->allowedTotals = array();

            if ($this->_getModuleHelper()->isShippingQuotesEnabled()) {
                $this->allowedTotals[] = 'shipping';
            }

            if ($this->_getModuleHelper()->isTaxEstimatesEnabled()) {
                $this->allowedTotals[] = 'tax';
            }
        }

        return (array)$this->allowedTotals;
    }

    /**
     * Render totals html for specific totals area (footer, body)
     *
     * @param   null|string $area
     * @param   int $colspan
     * @return  string
     */
    public function renderTotals($area = null, $colspan = 1)
    {
        $html = '';
        foreach ($this->getTotals() as $total) {
            if ($total->getArea() != $area && $area != -1) {
                continue;
            }

            if (!in_array($total->getCode(), $this->getAllowedTotals())) {
                continue;
            }

            $html .= $this->renderTotal($total, $area, $colspan);
        }

        if ($html && !$this->showBlock) {
            $this->showBlock = true;
            $html .= $this->_getHideBlock(self::MODE_TOTALS_SHOW)->toHtml();
        }
        return $html;
    }

    /**
     * Hide totals block if there are no totals.
     * Otherwise it will output an empty block.
     *
     * @param string $mode show|hide
     * @return Mage_Core_Block_Template
     */
    protected function _getHideBlock($mode)
    {
        if (!in_array($mode, array(self::MODE_TOTALS_HIDE, self::MODE_TOTALS_SHOW))) {
            Mage::throwException('Invalid argument.');
        }

        return $this->getLayout()->createBlock('core/template')
            ->setTemplate('request4quote/cart/hide_totals.phtml')
            ->setMode($mode);
    }

    /**
     * Hide totals block if there are no totals.
     * Otherwise it will output an empty block.
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = '';
        if (!$this->showBlock) {
            $html = $this->_getHideBlock(self::MODE_TOTALS_HIDE)->toHtml();
        }
        return $html . parent::_toHtml();
    }
}