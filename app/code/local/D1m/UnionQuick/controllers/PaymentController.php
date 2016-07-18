<?php
class D1m_UnionQuick_PaymentController extends Mage_Core_Controller_Front_Action
{
    protected $_order;

    /**
     * @return D1m_Sales_Model_Order
     */
    public function getOrder()
    {
        if ($this->_order == null)
        {
            $increment_id = trim($this->getRequest()->getParam('increment_id'));
            $session = Mage::getSingleton('checkout/session');
            $this->_order = Mage::getModel('sales/order');

            if ($session->getLastRealOrderId()){
                $this->_order->loadByIncrementId($session->getLastRealOrderId());
            } else{
                if (!empty($increment_id)){
                    $decodeOrderId = base64_decode($increment_id);
                    $this->_order->loadByIncrementId($decodeOrderId);
                }
            }
        }
        return $this->_order;
    }

    /**
     * Get checkout Session
     * @return Mage_Checkout_Model_Session
     */
    private function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    public function indexAction()
    {
        echo 'hello';
    }

    /**
     * Redirect to payment
     */
    public function redirectAction()
    {
        $session = $this->getCheckout();
        $session->setUnionQuickQuoteId($session->getQuoteId());

        if ($incrementId = $this->getRequest()->getParam('increment_id'))
        {
            $decodeOrderId = base64_decode($incrementId);
            $session->setLastRealOrderId($decodeOrderId);
        }

        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('d1m_unionquick/redirect')
                ->toHtml()
        );

        $session->unsQuoteId();
    }

    /**
     * Save invoice for order
     *
     * @param    Mage_Sales_Model_Order $order
     * @return	  boolean Can save invoice or not
     */
    protected function saveInvoice(Mage_Sales_Model_Order $order)
    {
        if ($order->canInvoice()) {
            /** @var Mage_Sales_Model_Convert_Order $convertor */
            $convertor = Mage::getModel('sales/convert_order');
            $invoice = $convertor->toInvoice($order);
            foreach ($order->getAllItems() as $orderItem) {
                if (! $orderItem->getQtyToInvoice()) {
                    continue;
                }
                $item = $convertor->itemToInvoiceItem($orderItem);
                $item->setQty($orderItem->getQtyToInvoice());
                $invoice->addItem($item);
            }
            $invoice->collectTotals();
            $invoice->register()->capture();
            Mage::getModel('core/resource_transaction')->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->save();
            return true;
        }

        return false;
    }

    /**
     * Notify by UnionPay
     */
    public function notifyAction()
    {
        /** @var D1m_UnionQuick_Model_Payment $model */
        $model = Mage::getModel('d1m_unionquick/payment');

        $data = $this->getRequest()->getPost();

        /** @var Mage_Sales_Model_Order $salesModel */
        $salesModel = Mage::getModel('sales/order');

        if (is_array($data)) {
            $arr_args       = $data;
            $cupReserved    = isset($arr_args['cupReserved']) ? $arr_args['cupReserved'] : '';
            parse_str(substr($cupReserved, 1, -1), $arr_reserved); //去掉前后的{}
        }
        else {
            $cupReserved = '';
            $pattern = '/cupReserved=(\{.*?\})/';
            if (preg_match($pattern, $data, $match)) { //先提取cupReserved
                $cupReserved = $match[1];
            }
            //将cupReserved的value清除(因为含有&, parse_str没法正常处理)
            $args_r         = preg_replace($pattern, 'cupReserved=', $data);
            parse_str($args_r, $arr_args);
            $arr_args['cupReserved'] = $cupReserved;
            parse_str(substr($cupReserved, 1, -1), $arr_reserved); //去掉前后的{}
        }

        //提取服务器端的签名
        if (!isset($arr_args['signature']) || !isset($arr_args['signMethod'])) {
            die('No signature Or signMethod set in notify data!');
        }
        $signature = $arr_args['signature'];

        unset($arr_args['signature']);
        unset($arr_args['signMethod']);

        //验证签名
        $verifySign = $model->sign($arr_args);

        if (trim($signature) != trim($verifySign)) {
            die('Bad signature returned!');
        }

        $args = array_merge($arr_args, $arr_reserved);
        unset($args['cupReserved']);

        $order = $salesModel->loadByIncrementId($args['orderNumber']);

        if($args['respCode'] == D1m_UnionQuick_Model_Payment::RESP_SUCCESS)
        {
            $order->addStatusToHistory($model->getConfigData('order_status_payment_accepted'),
                'Payment Accepted by UnionPay');
            if($this->saveInvoice($order))
            {
                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
            }

            //save order pay trade no
            $order->getPayment()->setPayTradeNo($args['qid'])->save();
            $order->setPayTradeNo($args['qid'])->save();

            Mage::dispatchEvent('order_send_to_cubic_ams_ready_unionquick', array('order' => $order));
        }
        else
        {
            $order->addStatusToHistory($model->getConfigData('order_status_payment_refused'),
                'Payment refused by UnionPay');
        }

        $order->save();
    }

    /**
     * Failure payment page
     *
     * @param    none
     * @return	  void
     */
    public function errorAction()
    {
        $order = $this->getOrder();

        if (! $order->getId()) {
            $this->norouteAction();
            return;
        }
        if ($order instanceof Mage_Sales_Model_Order && $order->getId()) {
            $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_CANCELED,
                'Customer returned from UnionPay');
            $order->save();
        }

        $this->loadLayout();
        $this->renderLayout();
        Mage::getSingleton('checkout/session')->unsLastRealOrderId();
    }
}