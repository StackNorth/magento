<?php

class D1m_Producttool_Block_Adminhtml_Downloadfile_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('downloadfile_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('d1m_producttool')->__('下载管理'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('d1m_producttool')->__('下载资料'),
          'title'     => Mage::helper('d1m_producttool')->__('下载资料'),
          'content'   => $this->getLayout()->createBlock('d1m_producttool/adminhtml_downloadfile_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}