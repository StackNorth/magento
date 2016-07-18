<?php
class D1m_UnionQuick_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    const CONSUME                = "01";
    const CONSUME_VOID           = "31";
    const PRE_AUTH               = "02";

    //region Union Related
    const PRE_AUTH_VOID          = "32";
    const PRE_AUTH_COMPLETE      = "03";
    const PRE_AUTH_VOID_COMPLETE = "33";
    const REFUND                 = "04";
    const REGISTRATION           = "71";
    const CURRENCY_CNY      = "156";

    const RESP_SUCCESS  = "00"; //返回成功

    const QUERY_SUCCESS = "0";  //查询成功
    const QUERY_FAIL    = "1";
    const QUERY_WAIT    = "2";
    const QUERY_INVALID = "3";

    protected $_code = 'unionquick_payment';
    protected $_formBlockType = 'd1m_unionquick/form';
    //endregion

    private $default_gateway = 'https://unionpaysecure.com/api/Pay.action';


    // Payment configuration
    protected $_isGateway               = false;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;

    // Order instance
    protected $_order = null;

    public function _construct()
    {
        bcscale(0);
        parent::_construct();
    }

    public function getStandardCheckoutFormFields()
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($this->getCheckout()->getLastRealOrderId());

        $this->getCheckout()->setLastOrderId($order->getId());

        $incrementId = $order->getIncrementId();
        $orderId = $order->getRealOrderId();

        if (!($order instanceof Mage_Sales_Model_Order)) {
            Mage::throwException($this->_getHelper()->__('Cannot retrieve order object'));
        }

        $amount = sprintf('%.2f', $order->getBaseGrandTotal());
      /*  if ($order->getBaseCurrencyCode() != 'CNY') {
            if ($convert = Mage::getSingleton('directory/currency')->load($order->getOrderCurrencyCode())) {
                if (!$convert->getRate('CNY'))
                    die('Unionpay supports Chinese Yuan Renminbi only, please contact administrator to setup currency rate conversion first.');
                $amount = sprintf('%.2f', $convert->convert($amount, "CNY"));
            }
        }*/

        $data = array(
            "version" => '1.0.0',
            "charset" => 'UTF-8', //UTF-8, GBK等
            "transType" => self::CONSUME,
            "origQid" => '',
            "merId" => $this->getConfigData('partner_id'),
            "merAbbr" => '魔方公寓',
            "acqCode" => '',  //收单机构填写
            "merCode" => '',  //收单机构填写
            "commodityUrl" => '',
            "commodityName" => '',
            "commodityUnitPrice" => '',
            "commodityQuantity" => sprintf('%d', $order->getTotalQtyOrdered()),
            "commodityDiscount" => '',
            "transferFee" => sprintf('%d', $order->getShippingAmount() * 100),
            "orderNumber" => $incrementId,
            "orderAmount" => sprintf('%d', $order->getBaseGrandTotal() * 100),
            "orderCurrency" => self::CURRENCY_CNY,
            "orderTime" => date('YmdHis'),
            "customerIp" => $order->getRemoteIp(),
            "customerName" => '',
            "defaultPayType" => '',
            "defaultBankNumber" => '',
            "transTimeout" => '',
            "frontEndUrl" => $this->getReturnURL(),
            "backEndUrl" => $this->getNotifyURL(),
            "merReserved" => '',
        );
        $data['signature'] = $this->sign($data);
        $data['signMethod'] = 'md5';

        return $data;
    }

    /**
     * Get the checkout session
     * @return Mage_Checkout_Model_Session
     */
    private function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get the return URL
     * @return string
     */
    protected function getReturnURL()
    {
        return Mage::getUrl('checkout/onepage/success', array('_secure' => true));
    }

    /**
     * Get the notification url for the payment gateway
     * @return string
     */
    protected function getNotifyURL()
    {
        return Mage::getUrl('unionquick/payment/notify', array('_secure' => true));
    }

    /**
     * Sign data with MD5
     * @param $params
     * @return string
     */
    public function sign($params)
    {
        ksort($params);
        $sign_str = "";
        foreach ($params as $key => $val) {
            if (in_array($key, array('bank'))) {
                continue;
            }
            $sign_str .= sprintf("%s=%s&", $key, $val);
        }
        return md5($sign_str . md5($this->getConfigData('security_code')));
    }

    /**
     * Return Unionpay Gateway
     * @return string
     */
    public function getUnionQuickURL()
    {
        if($this->getConfigData('gateway'))
        {
            return $this->getConfigData('gateway');
        }
        else
        {
            return $this->default_gateway;
        }
    }

    /**
     * Get the error url
     * @return string
     */
    protected function getErrorURL()
    {
        return Mage::getUrl('unionquick/payment/error', array('_secure' => true));
    }

    /**
     * Get the Order Redirect URL
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('unionquick/payment/redirect');
    }
}