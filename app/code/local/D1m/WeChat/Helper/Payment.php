<?php
/**
 *  微信支付的工具类
 * Class D1m_WeChat_Helper_Payment
 */
class D1m_WeChat_Helper_Payment extends Mage_Core_Helper_Abstract
{
    /***
     *  获得 order  state by status
     *
     * @param $orderStatus
     * @return mixed
     */
    public function getOrderState($status)
    {
        /* @var $orderStatus Mage_Sales_Model_Order_Status */
        $orderStatus = Mage::getModel('sales/order_status')->getCollection()
            ->addStatusFilter($status)->getFirstItem();

        if ($orderStatus)
        {
            return $orderStatus;
        }

        return ;
    }
    public  function getArvatoWechatUrl($backUrl){
        $appId='wx46cf70b550c7544c';
        $wxRedirect='http://wchat.qxlake.cn/TransferPage.aspx';
        return 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appId.'&redirect_uri='.$wxRedirect.'&response_type=code&scope=snsapi_base&state='.$backUrl.'&connect_redirect=1#wechat_redirect';
    }
}