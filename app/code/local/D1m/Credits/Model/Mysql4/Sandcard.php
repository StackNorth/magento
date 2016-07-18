<?php
/**
 * User: ahsw@qq.com
 * caeate Time: 2016/7/711:38
 */
class D1m_Credits_Model_Mysql4_Sandcard extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('d1m_credits/sandcard', 'id');
    }


    public function checkCardNum($cardNum)
    {

        $select = $this->_getReadAdapter()->select()->from(array('main_table'=>$this->getMainTable()))
            ->where('main_table.card_num=?', $cardNum) ;
        $info= $this->_getReadAdapter()->fetchRow($select);
        if($info){
            return $info['id'];
        }
        return 0;
    }
}