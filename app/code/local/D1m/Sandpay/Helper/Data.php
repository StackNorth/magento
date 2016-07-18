<?php
class D1m_Sandpay_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getSandCardNum($merchant_attach){
        preg_match('/accNo=(\d+)/i',$merchant_attach, $matches);
        return trim($matches[1]);

    }
}
