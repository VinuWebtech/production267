<?php
class ITwebexperts_Request4quote_Model_Resource_Quote_Collection extends Mage_Sales_Model_Resource_Quote_Collection {
	
	protected function _construct()
    {
        $this->_init('request4quote/quote');
    }

	public function addShippingAddress()
	{
        $_addressAlias = 'rfqa';
        $_joinTable = $this->getTable('request4quote/quote_address');


        $this->getSelect()->joinLeft(
            array($_addressAlias => $_joinTable),
            "main_table.entity_id = {$_addressAlias}.quote_id",
                array(
                    'company',
                    'telephone',
                    'postcode',
                    'country_id',
                    'region',
                    'city',
                    'street'
                )
            )->group("{$_addressAlias}.quote_id");
		//Mage::getResourceHelper('core')->prepareColumnsList($this->getSelect());
		return $this;
	}

    public function getSize()
    {
        if (count($this->getSelect()->getPart(Zend_Db_Select::GROUP)) > 0) {

            // Create a new collection from ids because we need a fresh select
            $ids = $this->getAllIds();
            $new_coll = Mage::getModel('request4quote/quote')->getCollection()
                ->addFieldToFilter('entity_id', array('in' => $ids));

            // return the collection size
            return $new_coll->getSize();
        }

        return parent::getSize();
    }
	
}