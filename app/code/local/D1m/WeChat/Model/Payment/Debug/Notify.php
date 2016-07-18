<?php
/**
 * Class D1m_WeChat_Model_Payment_Debug_Notify
 */
class D1m_WeChat_Model_Payment_Debug_Notify extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('weChat/payment_debug_notify');
    }

    /**
     *
     * @return Mage_Core_Model_Abstract|void
     */
    public function _beforeSave()
    {
        $nowDate  = Mage::getModel('core/date')->date('Y-m-d H:i:s');

        if (!$this->getId()) {
            $this->setCreatedTime($nowDate)
                ->setChangeTime($nowDate);
        }else{
            $this->setChangeTime($nowDate);
        }

        parent::_beforeSave();
    }
}
