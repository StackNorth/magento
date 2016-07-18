<?php
class D1m_WeChat_Block_Payment_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('weChat/payment/form.phtml');
        parent::_construct();
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }
}
