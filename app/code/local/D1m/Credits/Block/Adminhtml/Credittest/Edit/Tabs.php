<?php
class D1m_Credits_Block_Adminhtml_Credittest_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('credittest_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('d1m_credits')->__('General'));
      
    }
    
    protected function _prepareLayout()
    {
        /*$this->getLayout()->getBlock('head')
            ->addJs('pws/relatedproductsets/productLink.js');*/

        parent::_prepareLayout();
    }
   
   
   	public function getCredit()
    {
        return Mage::registry('current_credit_test');
    }

    protected function _beforeToHtml()
    {

    }
}
