<?php
class D1m_Integral_Block_Adminhtml_Integral_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
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

        $fieldset = $form->addFieldset('integral_form', array(
            'legend'=>Mage::helper('d1m_integral')->__('Integral Infomation')
        ));
        
        
        $this->_addElementTypes($fieldset);
        
        
         $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('username')
            ->addAttributeToSelect('email')
            ;


        $recordId  = (int) $this->getRequest()->getParam('id');
        if ($recordId==0)
        {

            $optionDatas = array();
            $optionDatas[] = '';
            foreach($collection as $customer)
            {
                $optionDatas[$customer->getId()] = $customer->getUsername();
            }

            $fieldset->addField('customer_id', 'select', array(
                    'label'    => Mage::helper('d1m_integral')->__('Customer'),
                    'scope'    => 'store',
                    'name'     => 'customer_id',
                    'required' => true,
                    'values'   => $optionDatas,
                    'note'	   => ''
                )
            );

        }
        else
        {

            $recordModel   = Mage::getModel('d1m_integral/integral');
            $record = $recordModel->load($recordId);
            $customer_id=$record->getData('customer_id');


            $collection->addFieldToFilter('entity_id',$customer_id);
            $item=$collection->getFirstItem();

            $fieldset->addField('customername', 'label', array(
                'name'      => 'customername',
                'label' =>  'customer name',
                'after_element_html'=>$item->getdata('username'),

            ));


            $fieldset->addField('customer_id', 'hidden', array(
                'name'      => 'customer_id',
                'required'  => false,
            ));


        }


        
        $fieldset->addField('credit_amount', 'text', array(
            'name'      => 'credit_amount',
        	'required'  => true,
            'label'     => Mage::helper('d1m_integral')->__('Credit Amount'),
        ));
        
        
		if(Mage::getSingleton('adminhtml/session')->getRecordData()){	
		    $record = Mage::getSingleton('adminhtml/session')->getRecordData();		
        	$form->setValues($record);
        	Mage::getSingleton('adminhtml/session')->setRecordData(false);
        } elseif(Mage::registry('integral')) {
            $form->setValues(Mage::registry('integral')->getData());
        }
        
        return parent::_prepareForm();
    }

    protected function _toHtml()
    {
        return parent::_toHtml();
    }

}
