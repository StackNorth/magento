<?php
/***
 *
 *  扫码支付的模板页面
 *
 * Class D1m_WeChat_Block_Payment_QrCode
 */
class D1m_WeChat_Block_Payment_QrCode extends Mage_Core_Block_Template
{
    protected $_payment;

    /**
     * @param mixed $payment
     */
    public function setPayment($payment)
    {
        $this->_payment = $payment;
        return $this;
    }

    /**
     * @return  D1m_WeChat_Model_Payment
     */
    public function getPayment()
    {
        return $this->_payment;
    }

    /**
     *
     * @return false|Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        $increment_id = trim($this->getRequest()->getParam('increment_id'));
        $session = Mage::getSingleton('checkout/session');
        $order = Mage::getModel('sales/order');

        if ($session->getLastRealOrderId()){
            $order->loadByIncrementId($session->getLastRealOrderId());
        } else{
            if (!empty($increment_id)){
                $decodeOrderId = base64_decode($increment_id);
                $order->loadByIncrementId($decodeOrderId);
            }
        }

        return $order;
    }

    /**
     *  获得所有的错误信息
     *
     * @return bool|mixed
     */
    public function getErrorMessages()
    {
        if (count(D1m_WeChat_Model_PromptMessage::getInstance()->getMessages()))
        {
            return D1m_WeChat_Model_PromptMessage::getInstance()->getMessages();
        }else{
            return false;
        }
    }

    protected function _construct()
    {
        $this->setTemplate('weChat/payment/qrCode.phtml');
        parent::_construct();
    }
}
