<?php
/***
 * Class D1m_GeeTest_Block_Geetest
 */
class D1m_GeeTest_Block_Captcha extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('geetest/geetest.phtml');
    }


    /**
     * Renders captcha HTML (if required)
     *
     * @return string
     */
    protected function _toHtml()
    {
        /* @var  $helper D1m_GeeTest_Helper_Data */
        $helper = Mage::helper('d1m_geeTest');

        if (strlen(trim($this->getFormId()))
            && $helper->isRequired($this->getFormId()))
        {
            return parent::_toHtml();
        }

        return '';
    }
}