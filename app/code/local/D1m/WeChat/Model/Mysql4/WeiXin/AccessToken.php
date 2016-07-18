<?php
/**
 * Class D1m_WeChat_Model_Mysql4_WeiXin_AccessToken
 */
class D1m_WeChat_Model_Mysql4_WeiXin_AccessToken extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('weChat/access_token', 'id');
    }
}
