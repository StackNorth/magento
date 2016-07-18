<?php
require_once "Mage/Checkout/controllers/CartController.php";

class Robi_Checkout_CartController extends Mage_Checkout_CartController
{
	
	/**
     * Set back redirect url to response
     *
     * @return Mage_Checkout_CartController
     * @throws Mage_Exception
     */
    protected function _goBack()
    {
        $this->_redirect('checkout/cart');
        return $this;
    }
	
	/**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }
	
	
	/**
     * Shopping cart display action
     */
    public function indexAction()
    {

    	if (!$this->_getCustomerSession()->isLoggedIn()) {
    		$url = Mage::getUrl('*/*');
            $this->_getCustomerSession()->setBeforeAuthUrl($url);
            $this->_redirect('customer/account/login');
            return;
        }
    	
        $cart = $this->_getCart();
        if ($cart->getQuote()->getItemsCount())
        {
            $cart->init();
            $cart->save();

            if (!$this->_getQuote()->validateMinimumAmount()) {
                $minimumAmount = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())
                    ->toCurrency(Mage::getStoreConfig('sales/minimum_order/amount'));

                $warning = Mage::getStoreConfig('sales/minimum_order/description')
                    ? Mage::getStoreConfig('sales/minimum_order/description')
                    : Mage::helper('checkout')->__('Minimum order amount is %s', $minimumAmount);

                $cart->getCheckoutSession()->addNotice($warning);
            }
        }

        // Compose array of messages to add
        $messages = array();
        foreach ($cart->getQuote()->getMessages() as $message) {
            if ($message) {
                // Escape HTML entities in quote message to prevent XSS
                $message->setCode(Mage::helper('core')->escapeHtml($message->getCode()));
                $messages[] = $message;
            }
        }
        $cart->getCheckoutSession()->addUniqueMessages($messages);

        /**
         * if customer enteres shopping cart we should mark quote
         * as modified bc he can has checkout page in another window.
         */
        $this->_getSession()->setCartWasUpdated(true);

        Varien_Profiler::start(__METHOD__ . 'cart_display');
        $this
            ->loadLayout()
            ->_initLayoutMessages('checkout/session')
            ->_initLayoutMessages('catalog/session')
            ->getLayout()->getBlock('head')->setTitle($this->__('购买课程'));
        $this->renderLayout();
        Varien_Profiler::stop(__METHOD__ . 'cart_display');
    }

    /**
     * Add product to shopping cart action
     *
     * @return Mage_Core_Controller_Varien_Action
     * @throws Exception
     */
    public function addAction()
    {

        //没登录先登录
        if (!$this->_getCustomerSession()->isLoggedIn())
        {
            $params=$this->getRequest()->getParams();
            $url=mage::geturl('*/*/*',$params);
             //die($url);
            $this->_getCustomerSession()->setBeforeAuthUrl($url);
            $this->_redirect('customer/account/login');
            return;
        }




        $params = $this->getRequest()->getParams();
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();




            /**
             * Check product availability
             */
            if (!$product)
            {
                $this->_goBack();
                return;
            }

            //不能购买过期课程
            //忽略秒
            $d1=substr($product->getClassDate(),0,10).' '.$product->getNClasstime1();
            $time  = mktime(date('H')+8, date('i'), 0, date("m") , date("d")+2, date("Y"));//UTC->GMT
            $d2=date("Y-m-d H:i",$time);
            if ($d1<$d2)
            {
                $message='课程已过期，不能预订(距开课时间小于48小时)';
                $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                $cart   = $this->_getCart();
                $cart->truncate(); //清空
                $cart->save();
                // $this->_goBack();
                retrun ;
            }

            //禁用的不可以订购
            if ($product->getStatus()!=Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            {
                $message='课程已预订满，不能购买';
                $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                $cart   = $this->_getCart();
                $cart->truncate(); //清空
                $cart->save();
                // $this->_goBack();
                retrun ;

            }
            
//            $this->_moveCartItems();
            $cart   = $this->_getCart();
            $cart->truncate(); //清空
            $cart->addProduct($product, $params);

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if (!$this->_getSession()->getNoCartRedirect(true)) {
                
                /**
                if (!$cart->getQuote()->getHasError()) {
                    $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
                    $this->_getSession()->addSuccess($message);
                }**/
                
                $this->_goBack();
            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                }
            }

            //$this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            $this->_goBack();
            
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
            Mage::logException($e);
            $this->_goBack();
        }
    }
    
    public function _moveCartItems()
    {
    	$returnData = null;
    	
    	$quote = Mage::getSingleton('checkout/cart')->getQuote();
    	$quote->removeAllItems();
        
        return $quote;
        
    }
	
	public function creditsPostAction()
    {
    	$used_amount = $this->getRequest()->getParam('use_credit_amount');
        settype($used_amount,"integer");
        if ($used_amount<0)
        {
                $message = $this->__('课点数为0或正整数');
                $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                $this->_redirect('checkout/cart');
                return;


        }
        $quote	  = Mage::helper('checkout/cart')->getCart()->getQuote();
        /* @var $quote Mage_Sales_Model_Quote  */
        $quote_id = $quote->getId();
        if ($quote_id<=0)
        {
            $message = $this->__('网页已过期失效');
            $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
            $this->_redirect('checkout/cart');
            return;
        }
		$allCredits = Mage::helper('settleaccount/credits')->getCreditsByCustomer();
        if($allCredits < $used_amount)
        	Mage::getSingleton('checkout/session')->addError($this->__('Credit is not enough.'));
        else
        	Mage::helper('settleaccount/event')->setPrepareUsedCredit($used_amount);
    	
    	$quote->collectTotals()->save();
    	
    	$this->_redirect('checkout/cart');
    	return $this;
    }
	
	public function pointsPostAction()
    {
    	$used_amount = $this->getRequest()->getParam('use_points_amount');
    	echo '$used_amount:'.$used_amount.'<br/>';
        $quote	  = Mage::helper('checkout/cart')->getCart()->getQuote();
        $quote_id = $quote->getId();
		$allCredits = Mage::helper('settleaccount/points')->getCurrentPoints();
        if($allCredits < $used_amount)
        	Mage::getSingleton('checkout/session')->addError($this->__('Points is not enough.'));
        else
        	Mage::helper('settleaccount/event')->setRewardPoints($used_amount);
    	
    	$quote->collectTotals()->save();
    	
    	$this->_redirect('checkout/cart');
    	return $this;
    }
    public function againPayAction(){
        $orderId=$this->getRequest()->getParam('oid');
        $orderModel = Mage::getModel('sales/order');
        $order= $orderModel->load($orderId);//ByIncrementId
        $order->getIncrementId();
        $redirectUrl = $order->getPayment()->getMethodInstance()->getOrderPlaceRedirectUrl();
        $session = Mage::getSingleton('checkout/session');
        $session->setLastRealOrderId($order->getIncrementId());
        if (isset($redirectUrl))
        {
            //订单提交，提示用户完成支付，如果不支付还能回到我的用户
            $go=Mage::getUrl('checkout/cart/wait').'?pid=1&qty=1&url='.urlencode($redirectUrl);
            $this->_redirectUrl($go);
        }
        else
        {
           $this->_redirect('checkout/cart');
        }


    }
    public function waitAction()
    {
        //等待支付
        $this->loadLayout()
            ->_initLayoutMessages('checkout/session')
            ->getLayout()->getBlock('head')->setTitle($this->__('等待支付'));
        $this->renderLayout();
   }
    public function couponPostAction()
    {
        //add code begin
        $quote	  = Mage::helper('checkout/cart')->getCart()->getQuote();
        /* @var $quote Mage_Sales_Model_Quote  */
        $quote_id = $quote->getId();
        if ($quote_id<=0)
        {
            $message = $this->__('网页已过期失效');
            $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
            $this->_redirect('checkout/cart');
            return;
        }
        //add code end

        /**
         * No reason continue with empty shopping cart
         */
        if (!$this->_getCart()->getQuote()->getItemsCount()) {
            $this->_goBack();
            return;
        }

        $couponCode = (string) $this->getRequest()->getParam('coupon_code');

        //对与用户绑定的coupon来进行验证
        if (!empty($couponCode)){
            //validate  rule and coupon code 此时只考虑rule 唯一性的情况
            $couponCode = trim($couponCode);
            $oCoupon = Mage::getModel('salesrule/coupon')->load($couponCode, 'code');
            $oRule = Mage::getModel('salesrule/rule')->load($oCoupon->getRuleId());

            if ($oRule && $oRule->getId() && $oRule->getIsCredits()==0) {
                $errors = Mage::getModel('couponRule/validator')->validateRuleCode($oRule,$oCoupon);
            } else {
                $errors = array('该优惠卷不存在。');
            }

            if(count($errors))
            {
                $this->_getSession()->addError( implode(',', $errors) );
                $this->_goBack();
                return;
            }
        }


        if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $this->_getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $this->_goBack();
            return;
        }

        try {
            $codeLength = strlen($couponCode);
            $isCodeLengthValid = $codeLength && $codeLength <= Mage_Checkout_Helper_Cart::COUPON_CODE_MAX_LENGTH;

            $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getQuote()->setCouponCode($isCodeLengthValid ? $couponCode : '')
                ->collectTotals()
                ->save();
           // print_r($this->_getQuote());die;
            if ($codeLength) {
                if ($isCodeLengthValid && $couponCode == $this->_getQuote()->getCouponCode()) {
                    $this->_getSession()->addSuccess(
                        $this->__('Coupon code "%s" was applied.', Mage::helper('core')->escapeHtml($couponCode))
                    );
                } else {
                    $this->_getSession()->addError(
                        $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->escapeHtml($couponCode))
                    );
                }
            } else {
                $this->_getSession()->addSuccess($this->__('Coupon code was canceled.'));
            }

        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
          //  print_r($this->_getQuote());die;
            $this->_getSession()->addError($this->__('Cannot apply the coupon code.'));
            Mage::logException($e);
        }

        $this->_goBack();
    }
	
	
	
}