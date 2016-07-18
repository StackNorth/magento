<?php
class D1m_Credits_Block_Adminhtml_Credits_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
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
        
        $form->setDataObject(Mage::registry('credits'));
        
        $this->setForm($form);
        
        //$dateFormatIso = Mage::app()->getLocale()->getDateTimeFormat(
        //    Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
        //);
        
        //$dateFormatIso = 'yyyy-MM-dd HH:mm:ss';

        //以后再改 下拉
        $fieldset = $form->addFieldset('credits_form', array(
            'legend'=>Mage::helper('d1m_credits')->__('Credits Infomation')
        ));
        
        
        $this->_addElementTypes($fieldset);
        /* @var $collection Mage_Customer_Model_Resource_Customer_Collection */
        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            //->addAttributeToSelect('username')
            ->addAttributeToSelect('email');
        $collection->setOrder('email');
         $recordId  = (int) $this->getRequest()->getParam('id');
        if ($recordId==0)
        {

            $optionDatas = array();
            $optionDatas[] = '';
            foreach($collection as $customer)
            {
                $optionDatas[$customer->getId()] = $customer->getEmail();
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
            $paymentPption=array(
                'alipay_payment'=>'支付宝',
                'chinapay_payment'=>'银联',
                'sandpay_payment'=>'杉德卡',
                'Coupons_Exchange'=>'优惠券',
                'cash_payments'=>'现金',
            );
            $fieldset->addField('payment_method', 'select', array(
                'name'      => 'payment_method',
                'required'  => true,
                'label'     => Mage::helper('d1m_credits')->__('payment Method'),
                'scope'    => 'store',
                'note'	   => '',
                'values'   => $paymentPption,

            ));

            $fieldset->addField('give_num', 'text', array(
                'name'      => 'give_num',
                'required'  => true,
                'label'     => Mage::helper('d1m_credits')->__('赠送课点数'),
            ));
            $fieldset->addField('subtotal', 'text', array(
                'name'      => 'subtotal',
                'required'  => true,
                'label'     => Mage::helper('d1m_credits')->__('付款金额'),


            ));
            $fieldset->addField('sales_promotion', 'text', array(
                'name' => 'sales_promotion',
                'label' => Mage::helper('adminhtml')->__('促销方案'),




            ));

        }
        else
        {

            $recordModel   = Mage::getModel('d1m_credits/credits');
            $record = $recordModel->load($recordId);
            $customer_id=$record->getData('customer_id');


            $collection->addFieldToFilter('entity_id',$customer_id);
            $item=$collection->getFirstItem();

            $fieldset->addField('customername', 'label', array(
                'name'      => 'customername',
                'label' =>  'customer name',
                'after_element_html'=>$item->getdata('username'),

            ));

            $fieldset->addField('sales_promotion', 'text', array(
                'name' => 'sales_promotion',
                'label' => Mage::helper('adminhtml')->__('促销方案'),
                'value' =>$item->getdata('sales_promotion'),



            ));

            $fieldset->addField('customer_id', 'hidden', array(
                'name'      => 'customer_id',
                'required'  => false,
            ));


        }
        $fieldset->addField('credit_amount', 'text', array(
            'name'      => 'credit_amount',
        	'required'  => true,
            'label'     => Mage::helper('d1m_credits')->__('购买课点数'),
        ));
        $fieldset->addField('order_from', 'select', array(
            'name'      => 'order_from',
            'required'  => true,
            'label'     => Mage::helper('d1m_credits')->__('订单来源'),
            'scope'    => 'store',
            'note'	   => '',
            'values'   => array('store'=>'门店','web'=>'网站','weixin'=>'微信' ),
        ));

        $fieldset->addField('order_trench', 'text', array(
            'name'      => 'order_trench',
            'required'  => true,
            'label'     => Mage::helper('d1m_credits')->__('购买门店'),
        ));
        $fieldset->addField('order_type', 'select', array(
            'name'      => 'order_type',
            'required'  => true,
            'label'     => Mage::helper('d1m_credits')->__('订单类型'),
            'scope'    => 'store',
            'note'	   => '',
            'values'   => array('buy'=>'购买','refund'=>'退还' ),
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
