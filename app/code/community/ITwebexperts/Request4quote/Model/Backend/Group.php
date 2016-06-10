<?php
class ITwebexperts_Request4quote_Model_Backend_Group extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    const ATTRIBUTE_CODE = 'r4q_showprice_group';

    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        if ($attributeCode == self::ATTRIBUTE_CODE) {
            $data = $object->getData($attributeCode);
            $postData = Mage::app()->getRequest()->getPost('product');
            if (!empty($postData) && empty($postData[$attributeCode])) {
                $data = array();
            }
            if (is_array($data)) {
                $object->setData($attributeCode, implode(',', $data));
            }
        }

        parent::beforeSave($object);
    }

    public function afterLoad($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        if ($attributeCode == self::ATTRIBUTE_CODE) {
            $data = $object->getData($attributeCode);
            if (!is_array($data)) {
                $object->setData($attributeCode, explode(',', $data));
            }
        }

        parent::afterLoad($object);
    }
}