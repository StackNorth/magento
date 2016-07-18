<?php
/**
 * Class D1m_WeChat_Model_System_Config_Source_Timeout
 */
class D1m_WeChat_Model_System_Config_Source_Timeout
{
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label' => Mage::helper('weChat')->__('No')),
            array('value' => 1, 'label' => Mage::helper('weChat')->__('Dynamic: According to Auto Cancellation')),
            array('value' => 2, 'label' => Mage::helper('weChat')->__('Specified Length')),
        );
    }

    public function toArray()
    {
        return array(
            0 => Mage::helper('adminhtml')->__('No'),
            1 => Mage::helper('weChat')->__('Dynamic: According to Auto Cancellation'),
            2 => Mage::helper('weChat')->__('Specified Length'),
        );
    }
}
