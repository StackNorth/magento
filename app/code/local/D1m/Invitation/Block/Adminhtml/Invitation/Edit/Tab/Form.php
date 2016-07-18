<?php
class D1m_Invitation_Block_Adminhtml_Invitation_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    
    /**
     * Prepares layout, set custom renderers
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

    }
    

    
    protected function _prepareForm()
    {
        
        $form = new Varien_Data_Form(
            array(
                'id'     => 'edit_form',
                'action' => $this->getActionUrl(),
                'method' => 'post',
                'field_name_suffix' => 'record',
                'enctype'=> 'multipart/form-data'
            )
        );
        
        $form->setDataObject(Mage::registry('integral'));
        
        $this->setForm($form);
        
        //$dateFormatIso = Mage::app()->getLocale()->getDateTimeFormat(
        //    Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
        //);
        
        $dateFormatIso = 'yyyy-MM-dd HH:mm:ss';

        $fieldset = $form->addFieldset('invitation_form', array(
            'legend'=>Mage::helper('d1m_invitation')->__('Invitation Infomation')
        ));
        
        
        $this->_addElementTypes($fieldset);
        
        
         $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('username')
            ->addAttributeToSelect('email')
            ;
        $optionDatas = array();
        $optionDatas[] = '';
        foreach($collection as $customer)
        {
        	$optionDatas[$customer->getId()] = $customer->getUsername();
        }
        
        $fieldset->addField('customer_id', 'select', array(
                'label'    => Mage::helper('d1m_invitation')->__('Customer'),
                'scope'    => 'store',
                'name'     => 'customer_id',
        		'required' => true,
                'values'   => $optionDatas,
                'note'	   => ''
             )
        );
        
        $fieldset->addField('name', 'text', array(
            'name'      => 'name',
        	'required'  => true,
            'label'     => Mage::helper('d1m_invitation')->__('name'),
        ));
        
        $fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('d1m_invitation')->__('Status'),
            'title'     => Mage::helper('d1m_invitation')->__('Status'),
            'name'      => 'status',
            'required'  => true,
            'options'   => array(
                '0' => Mage::helper('d1m_invitation')->__('Pending'),
                '1' => Mage::helper('d1m_invitation')->__('Registered'),
            ),
        ));
        
        $fieldset->addField('coupon_code', 'text', array(
            'name'      => 'coupon_code',
        	'required'  => false,
            'label'     => Mage::helper('d1m_invitation')->__('coupon_code'),
        ));
        
        $fieldset->addField('email', 'text', array(
            'name'      => 'email',
        	'required'  => true,
            'label'     => Mage::helper('d1m_invitation')->__('email'),
        ));
        
        $fieldset->addField('phone', 'text', array(
            'name'      => 'phone',
        	'required'  => true,
            'label'     => Mage::helper('d1m_invitation')->__('phone'),
        ));
        
        $fieldset->addField('note', 'text', array(
            'name'      => 'note',
        	'required'  => true,
            'label'     => Mage::helper('d1m_invitation')->__('note'),
        ));
        
        
		if(Mage::getSingleton('adminhtml/session')->getRecordData()){	
		    $record = Mage::getSingleton('adminhtml/session')->getRecordData();		
        	$form->setValues($record);
        	Mage::getSingleton('adminhtml/session')->setRecordData(false);
        } elseif(Mage::registry('invitation')) {
            $form->setValues(Mage::registry('invitation')->getData());
        }
        
        return parent::_prepareForm();
    }

    protected function _toHtml()
    {
        return parent::_toHtml();
    }

}
