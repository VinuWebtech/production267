<?php
class ITwebexperts_Request4quote_Model_Resource_Quote_Status extends Mage_Core_Model_Resource_Db_Abstract
{
	
	protected $_labelsTable;
	
	protected function _construct()
    {
        $this->_init('request4quote/quote_status', 'status');
		$this->_isPkAutoIncrement = false;
        $this->_labelsTable = $this->getTable('request4quote/quote_status_label');
    }

    public function getOptionArray()
    {
        $statusoptions = Mage::getModel('request4quote/quote_status')->getCollection();
        $statusArray = array();
        foreach($statusoptions as $status){
            $statusArray[$status->getStatus()] = $status->getLabel();
        }
        return $statusArray;
    }
	
	public function getStoreLabels(Mage_Core_Model_Abstract $status)
    {
        $select = $this->_getWriteAdapter()->select()
            ->from($this->_labelsTable, array('store_id', 'label'))
            ->where('status = ?', $status->getStatus());
        return $this->_getReadAdapter()->fetchPairs($select);
    }

	
	protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        if ($object->hasStoreLabels()) {
            $labels = $object->getStoreLabels();
            $this->_getWriteAdapter()->delete(
                $this->_labelsTable,
                array('status = ?' => $object->getStatus())
            );
            $data = array();
            foreach ($labels as $storeId => $label) {
                if (empty($label)) {
                    continue;
                }
                $data[] = array(
                    'status'    => $object->getStatus(),
                    'store_id'  => $storeId,
                    'label'     => $label
                );
            }
            if (!empty($data)) {
                $this->_getWriteAdapter()->insertMultiple($this->_labelsTable, $data);
            }
        }
        return parent::_afterSave($object);
    }
}