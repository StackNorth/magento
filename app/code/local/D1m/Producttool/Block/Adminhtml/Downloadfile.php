<?php
class D1m_Producttool_Block_Adminhtml_Downloadfile extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_downloadfile';
    $this->_blockGroup = 'd1m_producttool';
    $this->_headerText = Mage::helper('d1m_producttool')->__('课程下载资料管理');
    $this->_addButtonLabel = Mage::helper('d1m_producttool')->__('添加下载资料');
    parent::__construct();
  }
}