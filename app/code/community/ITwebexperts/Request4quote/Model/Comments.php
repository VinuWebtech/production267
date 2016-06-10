<?php
class ITwebexperts_Request4quote_Model_Comments extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('request4quote/comments');
    }

    public function getCommentByR4qId($quoteid){
        $commentsCollection = Mage::getModel('request4quote/comments')->getCollection()->addFieldToFilter('r4q_id',$quoteid);
        return $commentsCollection;
    }

    public function getCommentCustomerSide($quoteid){
        $commentsCollection = Mage::getModel('request4quote/comments')->getCollection()
            ->addFieldToFilter('r4q_id',$quoteid)
            ->addFieldToFilter('is_visible_on_front', 1);
        return $commentsCollection;
    }

    public function getFirstComment($quoteid){
        $commentsColl = $this->getCommentByR4qId($quoteid);
        $comment = $commentsColl->getFirstItem();
        return $comment->getComment();
    }

}