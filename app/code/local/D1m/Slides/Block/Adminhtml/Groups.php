<?php
/**
 * Created by Victor Guo
 * Date: 13-8-16
 * Time: 上午10:07
 */
class D1m_Slides_Block_Adminhtml_Groups extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function _construct()
    {
        $this->_controller = 'adminhtml_groups';
        $this->_blockGroup = 'd1m_slides';
        $this->_headerText = $this->__('Groups Management');
        $this->_addButtonLabel = $this->__('Add a Group');
        parent::_construct();
    }
}