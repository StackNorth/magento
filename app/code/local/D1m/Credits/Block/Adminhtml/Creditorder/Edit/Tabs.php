<?php
class D1m_Credits_Block_Adminhtml_Creditorder_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('creditorder_tabs');
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
        return Mage::registry('current_credit_order');
    }

    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('d1m_credits')->__('General'),
            'title'     => Mage::helper('d1m_credits')->__('General'),
            'content'   => $this->getLayout()->createBlock('d1m_credits/adminhtml_creditorder_edit_tab_form')->toHtml(),
        ));
        
        return parent::_beforeToHtml();
    }
}
