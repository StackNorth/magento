<?php

class D1m_Credits_Block_Adminhtml_Credittest_List extends Mage_Adminhtml_Block_Widget_Grid_Container
{      
    public function __construct()
    {
        $this->_controller = 'adminhtml_credittest';
        $this->_blockGroup = 'd1m_credits';
        //设置标题显示
        $this->_headerText = Mage::helper('d1m_credits')->__('Manage Credit Test');
        parent::__construct();
      //  $this->_removeButton('add');移除按钮


    }

    protected function _addBackButton()
    {   //添加按钮
        $this->_addButton('back', array(
            'label'     => $this->getBackButtonLabel(),
            'onclick'   => 'setLocation(\'' . $this->getBackUrl() .'\')',
            'class'     => 'back',
        ));
    }
    //创建按钮链接
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new');
    }

    public function getHeaderCssClass()
    {
        return 'icon-head ' . parent::getHeaderCssClass();
    }

    public function getHeaderWidth()
    {
        return 'width:50%;';
    }
}
