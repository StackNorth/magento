<?php
/**
 *  用户中心列出 与个人相关的全部优惠券
 */
class D1m_CouponRule_IndexController extends Mage_Core_Controller_Front_Action
{

    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $action = $this->getRequest()->getActionName();
        $loginUrl = Mage::helper('customer')->getLoginUrl();

        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }

    

    public function showAction() {

        // 1) security validate
       if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
    		$this->getResponse()->setRedirect("/customer/account");
    	}

        //2)到当前用户所属组可用的优惠规则
        $aviable_rule_ids   =  Mage::helper('couponRule')->getActiveRuleForCustomerGroup();
        $rule_ids = join(',', $aviable_rule_ids);

       $collection = Mage::getResourceModel('couponRule/coupon')->getAllCouponListForCustomer((int)Mage::getSingleton('customer/session')->getId(),$rule_ids);
       Mage::register('coupon_list', $collection);

        //3)init layout
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');

        //4)让优惠券选项标红，并设置标题
        if ($navigationBlock = $this->getLayout()->getBlock('customer_account_navigation')) {
               $navigationBlock->setActive('couponrule/index/show/');
           }

        $this->getLayout()->getBlock('head')->setTitle($this->__('My Account Coupon'));

        $this->renderLayout();
    }
    
}