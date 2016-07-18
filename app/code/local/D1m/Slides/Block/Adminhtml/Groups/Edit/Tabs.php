<?php
/**
 * Created by Victor Guo
 * Date: 13-8-16
 * Time: 上午10:52
 */

class D1m_Slides_Block_Adminhtml_Groups_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function _construct()
    {
        parent::_construct();
        $this->setId('groups_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Group Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('form_selection', array(
            'label' => $this->__('Group Information'),
            'alt' => $this->__('Group Information'),
            'content' => $this->getLayout()->createBlock('d1m_slides/adminhtml_groups_edit_tab_form')->toHtml()
        ));
        $this->addTab('slides_selection', array(
            'label' => $this->__('Slides'),
            'alt' => $this->__('Slides'),
            'content' => $this->getLayout()->createBlock('d1m_slides/adminhtml_groups_edit_tab_grid')->toHtml()
        ));
        return parent::_beforeToHtml();
    }
}