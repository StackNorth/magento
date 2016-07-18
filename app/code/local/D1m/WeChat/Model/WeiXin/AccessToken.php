<?php
/**
 * 数据库保存 access token
 *
 * Class D1m_WeChat_Model_WeiXin_AccessToken
 */
class D1m_WeChat_Model_WeiXin_AccessToken extends Mage_Core_Model_Abstract
{
    /**
     *
     * construct
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('weChat/weiXin_accessToken');
    }

    /**
     *  helper
     *
     * @return Mage_Core_Helper_Abstract
     */
    protected function _helper()
    {
        return Mage::helper('weChat');
    }

    /**
     * before save
     *
     * @return Mage_Core_Model_Abstract|void
     */
    protected function _beforeSave()
    {
        $nowDate  = Mage::getModel('core/date')->date('Y-m-d H:i:s');

        if (!$this->getId()) {
            $this->setCreatedTime($nowDate)
                ->setUpdateTime($nowDate);
        }else{
            $this->setUpdateTime($nowDate);
        }
        parent::_beforeSave();
    }

}