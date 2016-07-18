<?php
/***
 *
 * Class D1m_WeChat_Block_Payment_JsApi
 */
class D1m_WeChat_Block_Payment_JsApi extends Mage_Core_Block_Template
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
     *  get weChat Payment Json
     *
     * @return string
     */
    public function getWeChatPaymentJson()
    {
        $jsApiParameters = '';

        if (is_null($this->getPayment()))
        {
            return ;
        }

        /* @var $paymentModel D1m_WeChat_Model_Payment */
        $paymentModel = $this->getPayment();

        try{
            if ($paymentModel->enableJsApiPay())
            {
                $jsApiParameters = $paymentModel->getStandardCheckoutFormFields();
            }else{
                D1m_WeChat_Model_PromptMessage::getInstance()->setMessage('请确保您使用微信打开以及微信版本在5.0以上!');
            }
        }catch (Mage_Core_Exception $e)
        {
            D1m_WeChat_Model_PromptMessage::getInstance()->setMessage($e->getMessage());
        }

        return $jsApiParameters;
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
        $this->setTemplate('weChat/payment/pay.phtml');
        parent::_construct();
    }


}
