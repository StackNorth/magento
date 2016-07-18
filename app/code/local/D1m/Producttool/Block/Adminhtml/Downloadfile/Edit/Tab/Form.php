<?php
class D1m_Producttool_Block_Adminhtml_Downloadfile_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('downloadfile_form', array('legend'=>Mage::helper('d1m_producttool')->__('课程下载资料')));
      $id  = $this->getRequest()->getParam('id');
      if ($id)
      {

          $fieldset->addField('fid', 'label', array(
              'label'     => Mage::helper('d1m_producttool')->__('资料编号'),
              'class'     => 'required-entry',
              'name'      => 'fid',
              'after_element_html' => $id,
          ));

      }

      /*
      $fieldset->addField('pname', 'text', array(
          'label'     => Mage::helper('d1m_producttool')->__('课程名称'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'pname',
      ));
      */

      //改成下拉选择 ...

      $arrname=array();

      $productResource = Mage::getResourceSingleton('catalog/product');
      $attr = $productResource->getAttribute('name');
      $attrId = $attr->getAttributeId();
      $table1 = $attr->getBackend()->getTable();
      /* @var $resource Mage_Core_Model_Resource */
      $resource = Mage::getSingleton ('core/resource');

      $dbr=$resource->getConnection ('core_read' );
      $table2=$resource->getTableName('catalog_product_entity_varchar');


      $sql=" SELECT  DISTINCT `at_name`.`value` AS `name`
      FROM $table1 AS `e`
       LEFT JOIN $table2 AS `at_name`
       ON (`at_name`.`entity_id` = `e`.`entity_id`)
       AND (`at_name`.`attribute_id` = '$attrId')
       AND (`at_name`.`store_id` = 0)
       ORDER BY `name` ";

      $result = $dbr->fetchAll($sql);
      foreach($result as $item)
      {
          $v=$item['name'];
          $arrname[$v]=$v;
      }


      $fieldset->addField('pname', 'select', array(
          'label'     => Mage::helper('d1m_producttool')->__('课程名称'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'pname',
          'values'=>$arrname,
      ));

      if ( ($id=="") or ($id==0) )
      {

          $fieldset->addField('fupload', 'file', array(
              'label'     => Mage::helper('d1m_producttool')->__('上传资料'),
              'class'     => 'required-entry',
              'required'  => true,
              'name'      => 'fupload',
          ));

      }
      else
      {




          $fieldset->addField('fname', 'text', array(
              'label'     => Mage::helper('d1m_producttool')->__('资料名称'),
              'class'     => 'required-entry',
              'required'  => true,
              'name'      => 'fname',
          ));

          $fieldset->addField('fupload', 'file', array(
              'label'     => Mage::helper('d1m_producttool')->__('重新上传'),
              'name'      => 'fupload',
              'after_element_html' => '<br/>若更新下载资料请重新上传',
          ));


      }

      //设置值
      if ( Mage::registry('d1m_producttool_downloadfile_data') )
      {
          $form->setValues(Mage::registry('d1m_producttool_downloadfile_data')->getData());
      }
      return parent::_prepareForm();
  }
}