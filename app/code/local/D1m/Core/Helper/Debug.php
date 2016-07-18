<?php
class D1m_Core_Helper_Debug extends Mage_Core_Helper_Abstract
{

    /**
     * 出现异常等，发送信息发WEB MASTER
     */
    public function sendDebugMessageToWebMaster($title=NULL,$message=NULL){
        Mage::helper('d1m_webpower')->sendErrorReport($title,$message);
    }
}
