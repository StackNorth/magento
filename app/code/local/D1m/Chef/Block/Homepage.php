<?php
class D1m_Chef_Block_Homepage extends Mage_Core_Block_Template
{

    /**
     * chef list
     */
    public function _construct()
    {
        $collection = $this->_getCollection();
        $this->setCollection($collection);
    }

    protected function _getCollection()
    {
        if ( !$this->hasData('current_chef_chefs') )
        {
            if ( Mage::registry('current_chef_chefs') )
            {
                $this->setData('current_chef_chefs', Mage::registry('current_chef_chefs'));
            }
            else
            {
                $collection = Mage::getModel('d1m_chef/chef')->getCollection();
                $collection->addFieldToFilter('cstatus',D1m_Chef_Model_Status::STATUS_ENABLED);
                $collection->setOrder('corder', 'ASC');
                $this->setData('current_chef_chefs', $collection);
            }
        }
        return $this->getData('current_chef_chefs');
    }






}
