<?php


class Robi_Chinapay_Model_Source_Servicetype
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'trade_create_by_buyer', 'label' => Mage::helper('chinapay')->__('Products')),
            array('value' => 'create_direct_pay_by_user', 'label' => Mage::helper('chinapay')->__('Virtual Products')),
        );
    }
}



