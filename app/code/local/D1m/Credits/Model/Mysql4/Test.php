<?php
class D1m_Credits_Model_Mysql4_Test extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        
        $this->_init('d1m_credits/test', 'id');
    }

/*
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getCreatedAt()) {
            $object->setCreatedAt($this->formatDate(time()));
        }

        $object->setUpdatedAt($this->formatDate(time()));

        parent::_beforeSave($object);
    }

    public function getTestCredits($id)
    {
        $select = $this->_getReadAdapter()->select()->from(array('main_table'=>$this->getMainTable()), 'financial_money')
            ->where('main_table.id=?', $id);
        return $this->_getReadAdapter()->fetchAll($select);
        // return $this->_getReadAdapter()->fetchOne($select);
    }
*/
}

