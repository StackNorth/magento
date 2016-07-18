<?php
class D1m_Credits_Block_Adminhtml_Credits_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
                                        'id' => 'edit_form',
                                        'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                                        'method' => 'post',
                                        'enctype'=> 'multipart/form-data'
                                     )
        );

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
    
    /**
     * Retrieve Additional Element Types
     *
     * @return array
    
    protected function _getAdditionalElementTypes()
    {
        return array(
            'image' => Mage::getConfig()->getBlockClassName('uemall_event/adminhtml_event_helper_image')
        );
    }
     */
}
