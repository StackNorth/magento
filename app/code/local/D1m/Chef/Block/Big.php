<?php
class D1m_Chef_Block_Big extends Mage_Core_Block_Template
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
        if ( !$this->hasData('current_chef_big') )
        {
            if ( Mage::registry('current_chef_big') )
            {
                $this->setData('current_chef_big', Mage::registry('current_chef_big'));
            }
            else
            {
                $collection = Mage::getModel('d1m_chef/chef')->getCollection()
                    ->setOrder('corder', 'ASC');
                $this->setData('current_chef_big', $collection);
            }
        }
        return $this->getData('current_chef_big');
    }






}
