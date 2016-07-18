<?php
/***
 * 可配置
 * Class D1m_GeeTest_Model_Config
 */
class D1m_GeeTest_Model_Config extends Mage_Core_Model_Abstract
{
    /***
     *  captcha 的配置路径
     */
    const XML_PATH_CAPTCHA = 'geetest/captcha/';

    /**
     *  极验的帐户信息
     */
    const XML_PATH_ACCOUNT = 'geetest/account/';

    /***
     *  极验手机版的帐户信息
     */
    const XML_PATH_MOBILE_ACCOUNT = 'geetest/maccount/';

    /*****
     *  验证码-- 用户创建区域
     *  user_create
     */
    const CAPTCHA_FRONTEND_AREAS_FORM_USER_CREATE = 'user_create';
    /****
     *   验证码-- 用户登录
     */
    const CAPTCHA_FRONTEND_AREAS_FORM_USER_LOGIN   = 'user_login';
    /***
     *   验证码 --- 注册短信发送
     */
    const CAPTCHA_FRONTEND_AREAS_FORM_SEND_SMS      = 'send_register_sms';
    /***
     *   验证码 --- 加入学院
     */
    const CAPTCHA_FRONTEND_AREAS_FORM_JOIN_ACADEMY = 'join_academy';

    /***
     * @param null $storeId
     */
    public function isActive($storeId=null)
    {
        $currentStoreId = Mage::app()->getStore()->getId();
        if (is_null($storeId))
        {
            $storeId = $currentStoreId;
        }

        return Mage::getStoreConfigFlag(self::XML_PATH_CAPTCHA.'enable',$storeId);
    }

    /***
     *  获得验证码的基本信息
     *
     * @param $path
     * @param null $storeId
     * @return string
     */
    public function getCaptchaConfig($path,$storeId=null)
    {
        $currentStoreId = Mage::app()->getStore()->getId();
        if (is_null($storeId))
        {
            $storeId = $currentStoreId;
        }

        $configValue =  trim(Mage::getStoreConfig(self::XML_PATH_CAPTCHA.$path,$storeId));

        return $configValue;
    }

    /***
     *  获得 极验用户的基本信息
     *
     * @param $path
     * @param null $storeId
     * @return string
     */
    public function getAccountConfig($path,$storeId=null)
    {
        $currentStoreId = Mage::app()->getStore()->getId();
        if (is_null($storeId))
        {
            $storeId = $currentStoreId;
        }

        $configValue =  trim(Mage::getStoreConfig(self::XML_PATH_ACCOUNT.$path,$storeId));

        return $configValue;
    }

    /****
     * 获得 手机版极验用户的基本信息
     *
     * @param $path
     * @param null $storeId
     * @return string
     */
    public function getMAccountConfig($path,$storeId=null)
    {
        $currentStoreId = Mage::app()->getStore()->getId();
        if (is_null($storeId))
        {
            $storeId = $currentStoreId;
        }

        $configValue =  trim(Mage::getStoreConfig(self::XML_PATH_MOBILE_ACCOUNT.$path,$storeId));

        return $configValue;
    }
}