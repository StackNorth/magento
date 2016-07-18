<?php
/**
 * Created by Victor Guo
 * Date: 13-8-15
 * Time: 下午1:30
 */

class D1m_Slides_Block_Adminhtml_Slides extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function _construct()
    {
        $this->_controller = 'adminhtml_slides';
        $this->_blockGroup = 'd1m_slides';
        $this->_headerText = $this->__('Slides Management');
        $this->_addButtonLabel = $this->__('Add a Slide');
        parent::_construct();
    }
}