<?php

class D1m_Chef_Model_System_Config_Source_Chef extends Varien_Object
{
    protected function _initHelper()
    {
        return Mage::helper('d1m_chef');
    }

    public function getAllOptions() 
    {
        /* @var $model D1m_Chef_Model_Chef */

        $options[''] = '待定';//  $this->_initHelper()->__('Please Choose Chef');
        $model= Mage::getModel('d1m_chef/chef');

        /* @var $collection  D1m_Chef_Model_Mysql4_Chef_Collection */

        $collection =$model->getCollection();
        $collection
        ->setOrder('cregion')
        ->setOrder('corder')
        // ->setOrder('cstatus')
            // ->addFieldToFilter('cstats',D1m_Chef_Model_Status::STATUS_ENABLED)
            ;
        foreach ($collection as $item)
        {
             $options[$item->getId()] = $item->getCregion().' '.$item->getCname();
        }

        return $options;
    }

    
}
