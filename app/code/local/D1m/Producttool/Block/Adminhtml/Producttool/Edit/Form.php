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

class D1m_Producttool_Block_Adminhtml_Producttool_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {

        $step=$this->getRequest()->getParam('step','');
        $form = new Varien_Data_Form();

        /* @var $fieldset Varien_Data_Form_Element_Fieldset */
        if ($step == "")
           $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('adminhtml')->__('导入课程')));
        else if ($step == "pic")
            $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('adminhtml')->__('导入图片')));
        else
           $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('adminhtml')->__('导入课程第'.$step.'步')));

        if ($step == "")
        {
            $fieldset->addField('pdata', 'textarea', array(
                'name'  => 'pdata',
                'label' => Mage::helper('adminhtml')->__('导入数据'),
                'title' => Mage::helper('adminhtml')->__('导入数据'),
                'style' => 'width:600px;height:350px;',
                'required' => true,
                'after_element_html'=>'<br/>当前可以识别的数据列为:<br/> 名称	课程类型	省份	课程日期	课程时间	课程地址	座位数	菜式	要求	描述	SKU	价格',
                )
            );
            $fieldset->addField('psubmit','submit',  array(
                'name'=>'psubmit',
                'label'=>'',
                'value'=>'检查数据',
                'after_element_html'=>'',
                )
            );


            $fieldset->addField('pic', 'label',
                array(
                    'label' => $this->__('导入图片'),
                    'name'  => 'pic',
                    'after_element_html' => '在课程数据导入后，可以根据产品名称复制同名产品的图片。<br/><a href="'.$this->getUrl('*/*/pic').'">开始导入图片</a><br/>
                    注意：导入过程可能执行时间较长，导入中途请不要关闭或刷新页面。'
                )
            );

            $form->setAction($this->getUrl('*/*/save'));
            if (Mage::getSingleton('adminhtml/session')->getProducttoolData())
            {
                $form->setValues(Mage::getSingleton('adminhtml/session')->getProducttoolData());
                Mage::getSingleton('adminhtml/session')->setProducttoolData(null);
            }

        }
        else if ($step == '2')
        {

            $arrData=Mage::getSingleton('adminhtml/session')->getProducttoolTable();

            //var_dump($arrData);            die();

            $html='';
            if (is_array($arrData))
            {
                $html='<table border="0" cellpadding="2" cellspacing="2">';
                $html.='<tr><td>No.</td>';
                $brr=$arrData[0];
                for ($i=0;$i<count($brr);$i++)
                {
                    $html=$html.'<td><b>'.$brr[$i].'</b></td>';
                }
                $html.='</tr>';
                $html.="\r\n";

                for ($i=1;$i<count($arrData);$i++)
                {
                    $html.='<tr><td>'.$i.'</td>';
                    $brr=$arrData[$i];
                    for ($j=0;$j<count($brr);$j++)
                    {
                        $html=$html.'<td>'.$brr[$j].'</td>';
                    }
                    $html.='</tr>';
                    $html.="\r\n";
                }

                $html.='</table>';
            }
            //die($html);

            $fieldset->addField('pmsg','label',array(
                'name'=>'pmsg',
                'label'=>'导入数据',
                'after_element_html'=>        $html        ,
            ));

            $fieldset->addField('psubmit','submit',  array(
                'name'=>'psubmit',
                'label'=>'',
                'value'=>'确认导入',
                )
            );
            /*
            $fieldset->addField('pback','submit',  array(
                    'name'=>'pback',
                    'label'=>'',
                    'value'=>'上一步',
                    'onclick'=>'jscript:history.go(-1);return false;'
                )
            );
            */


            $form->setAction($this->getUrl('*/*/save/step/2'));


        }
        else if ($step == '3')
        {
            $fieldset->addField('pmsg','label',array(
                'name'=>'pmsg',
                'label'=>'导入完成',
            ));
        }
        else if ($step == 'pic')
        {
            $fieldset->addField('pmsg','label',array(
                'name'=>'pmsg',
                'label'=>'操作完成',
            ));
        }
        $form->setMethod('post');
        $form->setUseContainer(true);
        $form->setEnctype('multipart/form-data');

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
