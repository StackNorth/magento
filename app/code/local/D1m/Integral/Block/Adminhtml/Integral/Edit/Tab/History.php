<?php

class D1m_Integral_Block_Adminhtml_Integral_Edit_Tab_History extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('integral_history');
        $this->setDefaultSort('id');
        $this->setUseAjax(true);
    }

    public function getCredit()
    {
        return Mage::registry('current_integral');
    }

    protected function _addColumnFilterToCollection($column)
    {
        parent::_addColumnFilterToCollection($column);
        
        return $this;
    }

    protected function _prepareCollection()
    {
        
        $collection = Mage::getModel('d1m_integral/history')->getCollection()
            ->addFieldToFilter('credit_id', $this->getCredit()->getId());
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        
        $this->addColumn('id', array(
            'header'    => Mage::helper('d1m_integral')->__('ID'),
            'sortable'  => true,
            'width'     => '60',
            'index'     => 'id'
        ));
        
     
         $this->addColumn('add', array(
                'header'    => Mage::helper('d1m_integral')->__('add'),
                'align'     =>'left',
                'index'     =>'add',
                'filer-index'=> 'main_table.add',
             'sortable'  =>false,
        ));
        
         $this->addColumn('subtract', array(
                'header'    => Mage::helper('d1m_integral')->__('subtract'),
                'align'     =>'left',
                'index'     => 'subtract',
             'sortable'  =>false,
        ));
        
         $this->addColumn('description', array(
                'header'    => Mage::helper('d1m_integral')->__('description'),
                'align'     =>'left',
                'index'     => 'description'
        ));
        
         $this->addColumn('order_no', array(
                'header'    => Mage::helper('d1m_integral')->__('order No'),
                'align'     =>'left',
                'index'     => 'order_no'
        ));
        
         $this->addColumn('created_at', array(
                'header'    => Mage::helper('d1m_integral')->__('created at'),
                'align'     =>'left',
                'index'     => 'created_at',
                'type'  => 'datetime',



        ));
        
        
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }



}

