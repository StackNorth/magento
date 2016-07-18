<?php
/**
 * Created by Victor Guo
 * Date: 13-8-16
 * Time: 上午10:11
 */

class D1m_Slides_Block_Adminhtml_Groups_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected function _construct()
    {
        parent::_construct();

        $this->setId('groupsGrid');
        $this->setDefaultSort('created_time');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('d1m_slides/group')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('group_id', array(
            'header' => 'ID',
            'align' => 'center',
            'width' => '50px',
            'index' => 'group_id'
        ));

        $this->addColumn('group_name', array(
            'header' => $this->__('Name'),
            'align' => 'left',
            'index' => 'group_name'
        ));

        $this->addColumn('status', array(
            'header' => $this->__('Status'),
            'align' => 'center',
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                1 => $this->__('Enabled'),
                0 => $this->__('Disabled')
            )
        ));

        $this->addColumn('created_time', array(
            'header' => $this->__('Created Time'),
            'align' => 'center',
            'width' => '200px',
            'index' => 'created_time'
        ));

        $this->addColumn('action', array(
            'header' => $this->__('Action'),
            'width' => '80px',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => $this->__('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
        ));

        parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id'=>$row->getId()));
    }
}