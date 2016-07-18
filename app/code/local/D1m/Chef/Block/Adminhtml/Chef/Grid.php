<?php
class D1m_Chef_Block_Adminhtml_Chef_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('chefGrid');
        // $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('d1m_chef/chef')->getCollection();
        $collection->getSelect()->order('corder');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('corder', array(
            'header'    => $this->__('顺序'),
            'align'     =>'left',
            'index'     => 'corder',
            'width'     =>  '50',
        ));

        $this->addColumn('chef_id', array(
            'header'    => $this->__('编号'),
            'align'     =>'right',
            'width'     => '50',
            'index'     => 'chef_id',
        ));

        $this->addColumn('cname', array(
            'header'    => $this->__('姓名'),
            'align'     =>'left',
            'index'     => 'cname',
        ));
        $this->addColumn('coneword', array(
            'header'    => $this->__('简介'),
            'align'     =>'left',
            'index'     => 'coneword',
        ));

        $this->addColumn('cregion', array(
            'header'    => $this->__('城市'),
            'index'     => 'cregion',
            'align'     =>'left',
            //'type'      => 'options',
            //'options'   => array('上海'=>'上海','北京'=>'北京'),

        ));





        $this->addColumn('csmallpic', array(
            'header'    => $this->__('小图'),
            'align'     =>'left',
            'type' 	  => 'image',
            'index'     => 'csmallpic',
            'width'     =>  '100px',
            'renderer' => 'd1m_chef/adminhtml_chef_grid_column_renderer_image', //get the image HTML code
            'style' => 'text-align:center',
            'filter'    => false,
            'sortable'  => false,
        ));

        $this->addColumn('cbigpic', array(
            'header'    => $this->__('大图'),
            'align'     =>'left',
            'type' 	  => 'image',
            'index'     => 'cbigpic',
            'width'     =>  '100px',
            'renderer' => 'd1m_chef/adminhtml_chef_grid_column_renderer_image', //get the image HTML code
            'style' => 'text-align:center',
            'filter'    => false,
            'sortable'  => false,
        ));

        $this->addColumn('cstatus', array(
            'header'    => $this->__('Status'),
            'align'     => 'left',
            'width'     => '70',
            'index'     => 'cstatus',
            'type'      => 'options',
            'options'   => array(
                D1m_Chef_Model_Status::STATUS_ENABLED    => $this->__('Enabled'),
                D1m_Chef_Model_Status::STATUS_DISABLED   => $this->__('Disabled')
            ),
        ));

        $this->addColumn('action',
            array(
                'header'    =>  $this->__('Action'),
                'width'     => '60',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => $this->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    ),
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

//$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('chef_id');
        $this->getMassactionBlock()->setFormFieldName('chef_id');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'    => Mage::helper('d1m_chef')->__('Delete'),
            'url'      => $this->getUrl('*/*/massDelete'),
            'confirm'  => Mage::helper('d1m_chef')->__('Are you sure?')
        ));

        $statuses = array(
            D1m_Chef_Model_Status::STATUS_ENABLED   => Mage::helper('d1m_chef')->__('Enabled'),
            D1m_Chef_Model_Status::STATUS_DISABLED  => Mage::helper('d1m_chef')->__('Disabled')
        );
        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('chef_status', array(
            'label'=> Mage::helper('d1m_chef')->__('Change status'),
            'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'chef_status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('d1m_chef')->__('Status'),
                    'values' => $statuses
                )
            )
        ));
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}
