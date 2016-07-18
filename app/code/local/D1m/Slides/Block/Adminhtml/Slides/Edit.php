<?php
/**
 * Created by Victor Guo
 * Date: 13-8-15
 * Time: 下午3:35
 */
class D1m_Slides_Block_Adminhtml_Slides_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function _construct()
    {
        parent::_construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'd1m_slides';
        $this->_controller = 'adminhtml_slides';

        $this->_updateButton('save', 'label', $this->__('Save Slide'));
        $this->_updateButton('delete', 'label', $this->__('Delete Slide'));

        $this->_addButton('saveandcontinue', array(
            'label' => $this->__('Save and Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        return $this->__('Add/Edit Slide');
    }
}