<?php
class D1m_Common_Helper_Output extends Mage_Core_Helper_Abstract
{
    //以JSON的格式来返回
    public  function sendJsonResult($input)
    {
        $result = Mage::helper('core')->jsonEncode($input);
        Mage::app()->getResponse()->setHeader('Content-type', 'application/json');
        Mage::app()->getResponse()->setBody($result);
    }
}