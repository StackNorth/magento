<?php
/**
 * User: ahsw@qq.com
 * caeate Time: 2016/5/416:21
 */

class D1m_Credits_Block_Adminhtml_Order_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {


        // $form = new Varien_Data_Form();
        $form = new Varien_Data_Form(array(
                'id' => 'edit_form',
           //     'action' => $this->getUrl('*/*/courseOrder'),
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            )
        );



        $fieldset = $form->addFieldset('base_fieldset', array('legend' => Mage::helper('adminhtml')->__('设置订单价格')));

        $fieldset->addField('money', 'text', array(
            'name' => 'money',
            'label' => Mage::helper('adminhtml')->__('设置价格'),
            'value' => Mage::registry('current_order')->getFinancialMoney(),



        ));

      /*  $fieldset->addField('startDate', 'text', array(
                'name' => 'startDate',
                'label' => Mage::helper('adminhtml')->__('开始日期'),
                'title' => Mage::helper('adminhtml')->__('开始日期'),
                //'style' => 'width:600px;height:350px;',
                'required' => true,
                'after_element_html' => '<br/>请选择一个开始日期',
            )
        );

        $fieldset->addField('endtDate', 'text', array(
                'name' => 'endtDate',
                'label' => Mage::helper('adminhtml')->__('结束日期'),
                'title' => Mage::helper('adminhtml')->__('结束日期'),
                //'style' => 'width:600px;height:350px;',
                'required' => true,
                'after_element_html' => '<br/>请选择一个开结束日期',
            )
        );*/
    /*    $fieldset->addField('psubmit', 'submit', array(
                'name' => 'psubmit',
                'label' => '',
                'value' => '检查数据',
                'after_element_html' => '',
            )
        );*/
       // $form->setMethod('post');

        $form->setUseContainer(true);
     //   $form->setAction(true);
    //    $form->setEnctype('multipart/form-data');

        $this->setForm($form);

        return parent::_prepareForm();

    }

}
