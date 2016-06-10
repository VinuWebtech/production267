<?php
class ITwebexperts_Request4quote_Model_Resource_Cart extends Mage_Checkout_Model_Resource_Cart {
	
	protected function _construct()
    {
        $this->_init('request4quote/quote', 'entity_id');
    }
	
	public function fetchItemsSummary($quoteId)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from(array('q'=>$this->getTable('request4quote/quote')), array('items_qty', 'items_count'))
            ->where('q.entity_id = :quote_id');

        $result = $read->fetchRow($select, array(':quote_id' => $quoteId));
        return $result ? $result : array('items_qty'=>0, 'items_count'=>0);
    }
	
	public function fetchItems($quoteId)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from(array('qi'=>$this->getTable('request4quote/quote_item')),
                array('id'=>'item_id', 'product_id', 'super_product_id', 'qty', 'created_at'))
            ->where('qi.quote_id = :quote_id');

        return $read->fetchAll($select, array(':quote_id' => $quoteId));
    }
	
	
	public function addExcludeProductFilter($collection, $quoteId)
    {
        $adapter = $this->_getReadAdapter();
        $exclusionSelect = $adapter->select()
            ->from($this->getTable('request4quote/quote_item'), array('product_id'))
            ->where('quote_id = ?', $quoteId);
        $condition = $adapter->prepareSqlCondition('e.entity_id', array('nin' => $exclusionSelect));
        $collection->getSelect()->where($condition);
        return $this;
    }
	
}