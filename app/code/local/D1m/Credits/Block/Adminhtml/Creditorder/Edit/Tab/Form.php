<?php
class D1m_Credits_Block_Adminhtml_Creditorder_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
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
        
        $form->setDataObject(Mage::registry('credit_order'));
        
        $this->setForm($form);
        
        //$dateFormatIso = Mage::app()->getLocale()->getDateTimeFormat(
        //    Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
        //);
        
        $dateFormatIso = 'yyyy-MM-dd HH:mm:ss';

        $fieldset = $form->addFieldset('credit_order_form', array(
            'legend'=>Mage::helper('d1m_credits')->__('Credit order Infomation')
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
                'label'    => Mage::helper('d1m_credits')->__('Customer'),
                'scope'    => 'store',
                'name'     => 'customer_id',
        		'required' => true,
                'values'   => $optionDatas,
                'note'	   => ''
             )
        );
        
        $fieldset->addField('credit_amount', 'text', array(
            'name'      => 'credit_amount',
        	'required'  => true,
            'label'     => Mage::helper('d1m_credits')->__('Credit Amount'),
        ));
        
        
		if(Mage::getSingleton('adminhtml/session')->getRecordData()){	
		    $record = Mage::getSingleton('adminhtml/session')->getRecordData();		
        	$form->setValues($record);
        	Mage::getSingleton('adminhtml/session')->setRecordData(false);
        } elseif(Mage::registry('credits')) {
            $form->setValues(Mage::registry('credits')->getData());
        }
        
        return parent::_prepareForm();
    }

    protected function _toHtml()
    {
        return parent::_toHtml();
    }

}
