<?php
class D1m_Chef_Block_Detail extends Mage_Core_Block_Template 
{

    public function _construct()
    {
        $chef = $this->_getChef();
        $collection = $this->_getCollection();
        // $this->setChef($chef);
        $this->setCollection($collection);
    }

    protected function _getChef()
    {
        if ( !$this->hasData('current_chef_chef') ){
            if ( Mage::registry('current_chef_chef') ) {
                $this->setData('current_chef_chef', Mage::registry('current_chef_chef'));
            }
        }
        return $this->getData('current_chef_chef');
    }


    /**
     * Fetch chef product collection
     */
    protected function _getCollection() 
    {
        if ( !$this->hasData('current_chef_productCollection') ) {
            if ( Mage::registry('current_chef_productCollection') ) {
                $this->setData('current_chef_productCollection', Mage::registry('current_chef_productCollection'));
            }else{
                $collection = new Varien_Data_Collection();
                $this->setData('current_chef_productCollection', $collection);
            }
        }
        return $this->getData('current_chef_productCollection');
    }





}
