<?php
class D1m_Invitation_Block_Adminhtml_Invitation_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('invitation_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('d1m_invitation')->__('General'));
    }
    
    protected function _prepareLayout()
    {
        /*$this->getLayout()->getBlock('head')
            ->addJs('pws/relatedproductsets/productLink.js');*/

        parent::_prepareLayout();
    }
   
   
   	public function getCredit()
    {
        return Mage::registry('current_invitation');
    }

    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('d1m_invitation')->__('General'),
            'title'     => Mage::helper('d1m_invitation')->__('General'),
            'content'   => $this->getLayout()->createBlock('d1m_invitation/adminhtml_invitation_edit_tab_form')->toHtml(),
        ));
        
        return parent::_beforeToHtml();
    }
}
