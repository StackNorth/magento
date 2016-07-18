<?php

class D1m_Producttool_Block_Adminhtml_Downloadfile_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';

        $this->_blockGroup = 'd1m_producttool';
        $this->_controller = 'adminhtml_downloadfile';
        $this->removeButton('delete');
        //根据id参数

        $id  = $this->getRequest()->getParam('id');
        if ( ($id=="") or ($id==0) )
        {
            //增加时

            $this->_updateButton('save', 'label', Mage::helper('d1m_producttool')->__('保存'));
            //$this->_updateButton('delete', 'label', Mage::helper('d1m_producttool')->__('Delete Item'));


        }
        else
        {
            //修改时

            // $this->removeButton('save');


        }




        /*
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
        */
    }

    public function getHeaderText()
    {
        if( Mage::registry('d1m_producttool_downloadfile_data') && Mage::registry('d1m_producttool_downloadfile_data')->getId() )
        {
            return Mage::helper('d1m_producttool')->__("查看下载资料信息,编号 %d", $this->escapeHtml(Mage::registry('d1m_producttool_downloadfile_data')->getId()));
        }
        else
        {
            return Mage::helper('d1m_producttool')->__('下载管理');
        }
    }
}