<?php


class Robi_Chinapay_Model_Source_Transport
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'https', 'label' => Mage::helper('chinapay')->__('https')),
            array('value' => 'http', 'label' => Mage::helper('chinapay')->__('http')),
        );
    }
}