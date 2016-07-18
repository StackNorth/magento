<?php
/**
 * User: ahsw@qq.com
 * caeate Time: 2016/5/2710:09
 */

class D1m_Sandpay_ApiController extends Mage_Core_Controller_Front_Action
{
    private $_order = null;


    public function addDiscountMsgAction()
    {
        $cards = $this->getRequest()->getParam('cards','');
        $data='<?xml version="1.0" encoding="utf-8"?>
<root>
    <row>
        <crad>111></crad>
        <discount>打了9折</discount>
    </row>
    <row>
        <crad>222></crad>
        <discount>打了9.8折</discount>
    </row>
    <row>
        <crad>333></crad>
        <discount>打了9.5折</discount>
    </row>
</root>';

        $xml = simplexml_load_string($data);
        print_r($xml);die;
        $xmlArray= json_decode(json_encode($xml),TRUE);
        print_r($xmlArray);

    }

}