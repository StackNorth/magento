<?php
/**
 * Created by PhpStorm.
 * User: d1m
 * Date: 2016/7/14
 * Time: 17:40
 */
class D1m_Credits_Model_Test extends Mage_Core_Model_Abstract
{   public $historyDesc = null;
    public $historyOrderNo = null;
    public $_historyRec = null;

    protected function _construct()
    {
        
        $this->_init('d1m_credits/test');
    }

    /*public function getStatus()
    {
        $dbr = Mage::getSingleton( 'core/resource' )->getConnection( 'core_read' );
        $sql = "select status from aca_credits_test where id = '".$this->getId()."'";
        return $this->Status = $dbr->fetchOne($sql);


    }*/
}