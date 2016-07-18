<?php
class D1m_Credits_Block_Adminhtml_Credittest_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('credittestGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }



    protected function _prepareCollection()
    {

        $collection = Mage::getResourceModel('d1m_credits/test_collection');//在Model下的Mysql下的Test下的Collection文件
        $collection->getSelect()->reset('columns')->columns(array('id','status','title','content','email','time','img','type'));//获取数据


        $this->setCollection($collection);

        return parent::_prepareCollection();
    }
    //设置表头显示的列
    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('d1m_credits')->__('编号'),
            'align'     =>'left',
            'width'     => '50px',
            'index'     => 'id',
        ));
        //添加列数据
        $this->addColumn('status', array(
            'header'    => Mage::helper('d1m_credits')->__('状态'),
            'align'     => 'left',
            'index'     => 'status',

        ));
        $this->addColumn('title', array(
            'header'    => Mage::helper('d1m_credits')->__('标题'),
            'align'     => 'left',
            'index'     => 'title',

        ));
        $this->addColumn('content', array(
            'header'    => Mage::helper('d1m_credits')->__('内容'),
            'align'     => 'left',
            'index'     => 'content',

        ));
        $this->addColumn('test_type', array(
            'header'    => Mage::helper('d1m_credits')->__('文章类型'),
            'align'     => 'left',
            'index'     => 'type',
           

        ));
        $this->addColumn('email', array(
            'header'    => Mage::helper('d1m_credits')->__('邮箱'),
            'align'     => 'left',
            'index'     => 'email',

        ));
        $this->addColumn('time', array(
            'header'    => Mage::helper('d1m_credits')->__('时间'),
            'align'     => 'left',
            'index'     => 'time',

        ));
        $this->addColumn('testimg', array(
            'header'    => $this->__('图片'),
            'align'     =>'left',
            'type' 	  => 'image',
            'index'     => 'img',
            'width'     =>  '100px',
            'renderer' => 'd1m_credits/adminhtml_credittest_grid_column_renderer_image', //get the image HTML code
            'style' => 'text-align:center',
            'filter'    => false,
            'sortable'  => false,
        ));
        
        //添加链接，
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

      
        return parent::_prepareColumns();
    }



    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'    => Mage::helper('d1m_credits')->__('Delete'),
            'id'       => Mage::getModel('d1m_credits/test')->getId(),
            'url'      => $this->getUrl('*/*/massDelete'),
            'confirm'  => Mage::helper('d1m_credits')->__('Are you sure?')
        ));


        return $this;
    }


    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }





}
