<?php
class D1m_Integral_Block_Adminhtml_Integral_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('integral_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('d1m_integral')->__('General'));
    }
    
    protected function _prepareLayout()
    {
        /*$this->getLayout()->getBlock('head')
            ->addJs('pws/relatedproductsets/productLink.js');*/

        parent::_prepareLayout();
    }
   
   
   	public function getCredit()
    {
        return Mage::registry('current_integral');
    }

    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('d1m_integral')->__('General'),
            'title'     => Mage::helper('d1m_integral')->__('General'),
            'content'   => $this->getLayout()->createBlock('d1m_integral/adminhtml_integral_edit_tab_form')->toHtml(),
        ));
        
        $credit = $this->getCredit();
        if($credit && $credit->getId())
        {
        	$this->addTab('history_section', array(
	            'label'     => Mage::helper('d1m_integral')->__('History'),
	            'title'     => Mage::helper('d1m_integral')->__('History'),
	            'content'   => $this->getLayout()->createBlock('d1m_integral/adminhtml_integral_edit_tab_history')->toHtml(),
	        ));
        	
        }
       
        return parent::_beforeToHtml();
    }
}
