<?php

class D1m_Producttool_Block_Adminhtml_Downloadfile_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('downloadfileGrid');
      $this->setDefaultSort('id');
      //$this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('d1m_producttool/downloadfile')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }


  protected function _prepareColumns()
  {

      $this->addColumn('id', array(
          'header'    => Mage::helper('d1m_producttool')->__('编号'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'id',
      ));

      $this->addColumn('pname', array(
          'header'    => Mage::helper('d1m_producttool')->__('课程名称'),
          'align'     =>'left',
          'index'     => 'pname',
      ));

      $this->addColumn('fname', array(
          'header'    => Mage::helper('d1m_producttool')->__('资料名称'),
          'align'     =>'left',
          'index'     => 'fname',
      ));


	  /*
      $this->addColumn('content', array(
			'header'    => Mage::helper('d1m_producttool')->__('Item Content'),
			'width'     => '150px',
			'index'     => 'content',
      ));
	  */

      /*
      $this->addColumn('status', array(
          'header'    => Mage::helper('d1m_producttool')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
	  */

/*
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('d1m_producttool')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',

                //下面的是下拉
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('d1m_producttool')->__('查看'),
                        'url'       => array('base'=> 'z/z/edit'),
                        'field'     => 'id'
                    )
                //,     array(
//                        'caption'   => Mage::helper('d1m_producttool')->__('下载'),
//                        'url'       => array('base'=> 'z/z/download'),
//                        'field'     => 'id'                    )
                ),

                'filter'    => false,
                'sortable'  => false,
                'is_system' => true,
        )
        );

*/
      $this->addColumn('action2', array(
          'header'    => '操作',
          'align'     =>'center',
           'format'    =>'
           <a target=_blank href="'.$this->getUrl('*/*/download/id/$id').'">下载</a>
           <a href="'.$this->getUrl('*/*/delete/id/$id').'" onclick="return confirm(\'确认操作吗？\')">删除!</a> ',
          'filter'    =>false,
          'sortable'  =>false,
          'is_system' =>true
      ));

       // $this->setColumnFilter('id');//不起作用！ todo
           // ->setColumnFilter('email')          ->setColumnFilter('name');

		$this->addExportType('*/*/exportCsv', Mage::helper('d1m_producttool')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('d1m_producttool')->__('XML'));

      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('downloadfile_id'); //表单域

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('d1m_producttool')->__('批量删除'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('d1m_producttool')->__('确认删除所选记录吧？(删除记录时会删除对应的文件)')
        ));

        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}