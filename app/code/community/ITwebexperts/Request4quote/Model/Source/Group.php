<?php
class ITwebexperts_Request4quote_Model_Source_Group extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{

    public function getAllOptions()
    {
        $groups = Mage::getResourceModel('customer/group_collection')
            ->addFieldToFilter('customer_group_id', array('gt'=> 0))
            ->load()
            ->toOptionArray();

        array_unshift($groups, array(
            'value' => 100000, // Set to 100000 because if 0, can't get data from attribute in product listing
            'label' => Mage::helper('core')->__('All Customer Groups')
        ));

        return $groups;
    }
}
