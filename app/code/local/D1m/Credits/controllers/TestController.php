<?php
/**
 * Created by PhpStorm.
 * User: d1m
 * Date: 2016/7/18
 * Time: 13:56
 */
class D1m_Credits_TestController extends  Mage_Core_Controller_Front_Action
{
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }


    protected function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    public function viewAction()
    {
        $this->loadLayout();

       /* $this->_initLayoutMessages('checkout/session');*/
        $this->getLayout()->getBlock('head')->setTitle($this->__('测试数据'));
        $this->renderLayout();
    }
 
}