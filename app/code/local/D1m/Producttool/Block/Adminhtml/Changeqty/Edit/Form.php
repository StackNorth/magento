<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml edit admin user account form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class D1m_Producttool_Block_Adminhtml_Changeqty_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {

        $form = new Varien_Data_Form();

        /* @var $fieldset Varien_Data_Form_Element_Fieldset */
        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('adminhtml')->__('调整课程数量或状态')));

        $arr=array(''=>'不限');

        $model= Mage::getModel('catalog/product');
        $collection =$model->getCollection();
        /* @var $model Mage_Catalog_Model_Product */;
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */

        $collection->addAttributeToSelect('name');
        $collection->setOrder('name','asc')  ;
        $collection->setOrder('entity_id','desc')  ;
        $lastname='';
        foreach ($collection as $item)
        {
            $name=$item->getData('name');
            if ($name!=$lastname)
            {
                $pid=$item->getId();
                $arr[$pid]=$name;
                $lastname=$name;
            }
        }


        $fieldset->addField('pname2', 'select', array(
                'name'  => 'pname2',
                'label' => Mage::helper('adminhtml')->__('课程名称'),
                'title' => Mage::helper('adminhtml')->__('课程名称'),
                'required' => false,
                'values' => $arr,
                // 'onchange'=>'alert(\'on change\')',
            )
        );


        $arrday=array();
        //列出90日的课程,周几

        $t=mktime(0,0,0,date('n'),date('j'),date("Y"));
        $arrweekday=array('星期天','星期一','星期二','星期三','星期四','星期五','星期六');
        for ($i=0;$i<=90;$i++)
        {
            $day=date("Y-m-d",$t);
            $weekday=date("w",$t);
            $arrday[]= array( 'value' => $day, 'label' =>$day.'('.$arrweekday[$weekday].')');
            $t=$t+86400;
        }
        //var_dump($arrday);        die();

        $fieldset->addField('pday', 'multiselect', array(
                'name'  => 'pday',
                'label' => Mage::helper('adminhtml')->__('开课日期'),
                'title' => Mage::helper('adminhtml')->__('开课日期'),
                'after_element_html' => '<br/><small>使用shift,ctrl可多选</small>',
                'required' => true,
                // 'class'     => 'required-entry',
                'values' => $arrday,
            )
        );


        $fieldset->addField('ptime1', 'text', array(
                'name'  => 'ptime1',
                'label' => Mage::helper('adminhtml')->__('开始时间hh:mm'),
                'title' => Mage::helper('adminhtml')->__('开始时间'),
                'required' => false,
                'value'=>'00:00',
            )
        );
        $fieldset->addField('ptime2', 'text', array(
                'name'  => 'ptime2',
                'label' => Mage::helper('adminhtml')->__('结束时间hh:mm'),
                'title' => Mage::helper('adminhtml')->__('结束时间'),
                'required' => false,
                'value'=>'23:59',
            )
        );





        $arrcity=array(''=>'不限');
        $attribute_code = 'province';
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product',$attribute_code);
        $options = $attribute->getSource()->getAllOptions();
        foreach ($options as $option)
        {
            if ($option['value'] != "")
            {
                $key=$option['value'];
                $value  =$option['label'];
                $arrcity[$key]=$value;

            }
        }

        $fieldset->addField('pcity', 'select', array(
            'label'     =>  Mage::helper('adminhtml')->__('城市'),
            'title' => Mage::helper('adminhtml')->__('城市'),
            //'class'     => 'required-entry',
            'required'  => false,
            'name'      => 'pcity',
            'value'  => '',
            'values' => $arrcity,
            //'after_element_html' =>'',
        ));



        $fieldset->addField('status', 'select', array(
            'label'     =>  Mage::helper('adminhtml')->__('改变后的状态*'),
            'title' => Mage::helper('adminhtml')->__('状态'),
            'required'  => false,
            'name'      => 'status',
            'value'  => '',
            'values' =>
             array(
                0=>'不变',
                1 => '启用',
                2 => '禁用',
            ),
            //'after_element_html' =>'',
        ));

        $fieldset->addField('pqty', 'text', array(
                'name'  => 'pqty',
                'label' => Mage::helper('adminhtml')->__('改变后的库存数(留空不变)*'),
                'title' => Mage::helper('adminhtml')->__('库存数'),
                'required' => false,
            )
        );


        $fieldset->addField('psubmit','submit',  array(
                'name'=>'psubmit',
                'label'=>'',
                'value'=>'提交',
                'after_element_html'=>'',
            )
        );


        //$fieldset->addField('user_id', 'hidden', array(  'name'  => 'user_id',            )        );


//        $form->setValues($user->getData());

        $form->setAction($this->getUrl('*/*/save'));
        $form->setMethod('post');
        $form->setUseContainer(true);
        $form->setId('edit_form');

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
