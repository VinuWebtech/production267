<?php
class ITwebexperts_Request4quote_Model_Resource_Quote extends Mage_Sales_Model_Resource_Quote
{
	protected function _construct()
    {
        $this->_init('request4quote/quote', 'entity_id');
    }
	
	public function markQuotesRecollectOnCatalogRules()
    {
        $tableQuote = $this->getTable('request4quote/quote');
        $subSelect = $this->_getReadAdapter()
            ->select()
            ->from(array('t2' => $this->getTable('request4quote/quote_item')), array('entity_id' => 'quote_id'))
            ->from(array('t3' => $this->getTable('catalogrule/rule_product_price')), array())
            ->where('t2.product_id = t3.product_id')
            ->group('quote_id');

        $select = $this->_getReadAdapter()->select()->join(
            array('t2' => $subSelect),
            't1.entity_id = t2.entity_id',
            array('trigger_recollect' => new Zend_Db_Expr('1'))
        );

        $updateQuery = $select->crossUpdateFromSelect(array('t1' => $tableQuote));

        $this->_getWriteAdapter()->query($updateQuery);

        return $this;
    }
	
	
	
	public function substractProductFromQuotes($product)
    {
        $productId = (int)$product->getId();
        if (!$productId) {
            return $this;
        }
        $adapter   = $this->_getWriteAdapter();
        $subSelect = $adapter->select();

        $subSelect->from(false, array(
            'items_qty'   => new Zend_Db_Expr(
                $adapter->quoteIdentifier('q.items_qty') . ' - ' . $adapter->quoteIdentifier('qi.qty')),
            'items_count' => new Zend_Db_Expr($adapter->quoteIdentifier('q.items_count') . ' - 1')
        ))
        ->join(
            array('qi' => $this->getTable('request4quote/quote_item')),
            implode(' AND ', array(
                'q.entity_id = qi.quote_id',
                'qi.parent_item_id IS NULL',
                $adapter->quoteInto('qi.product_id = ?', $productId)
            )),
            array()
        );

        $updateQuery = $adapter->updateFromSelect($subSelect, array('q' => $this->getTable('request4quote/quote')));

        $adapter->query($updateQuery);

        return $this;
    }
	
	
	public function markQuotesRecollect($productIds)
    {
        $tableQuote = $this->getTable('request4quote/quote');
        $tableItem = $this->getTable('request4quote/quote_item');
        $subSelect = $this->_getReadAdapter()
            ->select()
            ->from($tableItem, array('entity_id' => 'quote_id'))
            ->where('product_id IN ( ? )', $productIds)
            ->group('quote_id');

        $select = $this->_getReadAdapter()->select()->join(
            array('t2' => $subSelect),
            't1.entity_id = t2.entity_id',
            array('trigger_recollect' => new Zend_Db_Expr('1'))
        );
        $updateQuery = $select->crossUpdateFromSelect(array('t1' => $tableQuote));
        $this->_getWriteAdapter()->query($updateQuery);

        return $this;
    }
}