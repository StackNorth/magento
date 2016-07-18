<?php
/**
 *
 * Payment  Cron
 */
class D1m_WeChat_Model_Cron_Payment extends Varien_Object
{
    /***
     *  微信支付，没有notify的order
     *
     */
    const  PAYMENT_LOG_NO_NOTIFY_LOG_PATH = 'wechat.payment.log';

    /***
     *
     *  log no notify message
     *
     * @param $message
     * @param null $level
     */
    static function  logNoNotify($message,$level=null)
    {
        Mage::log($message,$level,self::PAYMENT_LOG_NO_NOTIFY_LOG_PATH);
    }

    /****
     * 检查是否有微信支付的订单是否没有notify成功
     *
     *  check weChat Payment
     */
    public function checkWeChatPay()
    {
        /* @var $payment D1m_WeChat_Model_Payment */
        $payment = Mage::getModel('weChat/payment');

        //1)check
        if (!$payment->getConfigData('enable_query_order'))
        {
            return ;
        }

        //2)默认检查90天之内
        $currentDay  = Mage::getModel('core/date')->date('Y-m-d');
        $startDay    = Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s',strtotime('-90 days'.$currentDay));

        /* @var  $orderCollection Mage_Sales_Model_Resource_Order_Collection */
        $orderCollection = Mage::getModel('sales/order')->getCollection();

        $orderCollection->addFieldToFilter('status',$payment->getConfigData('order_status'))
        ->addFieldToFilter('created_at',array('gteq'=>$startDay))
        ->addFieldToFilter('created_at',array('lteq'=>$currentDay));

        $orderCollection->getSelect()->join(array('sfop'=>'sales_flat_order_payment'),' sfop.parent_id=main_table.entity_id',
        array(
            'order_payment_method'=>'sfop.method',
        ))->where('sfop.method=?',$payment->getCode());

        //deal with payment
        /* @var $order Mage_Sales_Model_Order */
        foreach($orderCollection as $order)
        {
            /* @var $payment D1m_WeChat_Model_Payment */
            $payment = $order->getPayment()->getMethodInstance();
            $result  = $payment->getQueryOrderInfo($order);

            if (isset($result["result_code"]) && $result["result_code"] == "SUCCESS"
                && isset($result["trade_state"]) && in_array($result["trade_state"],D1m_WeChat_Model_Payment::getPaySuccessStatus()))
            {
                 $payment->updateOrder($result,$order);
                 self::logNoNotify('query to sync order success, the number is '.$order->getIncrementId());
            }
        }
    }
}