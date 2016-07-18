<?php
/**
 * Created by Victor Guo
 * Date: 13-8-15
 * Time: 下午4:27
 */
class D1m_Slides_Block_Adminhtml_Slides_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function _construct()
    {
        parent::_construct();
        $this->setId('slide_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Slide Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('form_selection', array(
            'label' => $this->__('Slide Information'),
            'alt' => $this->__('Slide Information'),
            'content' => $this->getLayout()->createBlock('d1m_slides/adminhtml_slides_edit_tab_form')->toHtml()
        ));
        return parent::_beforeToHtml();
    }
}