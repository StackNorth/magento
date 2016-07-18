<?php
class D1m_Credits_Block_Adminhtml_Credits_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('creditsGrid');
        $this->setDefaultDir('Desc');
        $this->setSaveParametersInSession(true);
    }
    
    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('d1m_credits/credits_collection')        ;

        $resource = Mage::getSingleton('core/resource');


        $customerTable = $resource->getTableName('customer_entity');
        $collection->getSelect()
            // ->reset('columns')
            // ->columns(array('id','status','qty','unit_price','gift_credits','grand_total','payment_method','created_at','updated_at','payment_method'))
            ->joinInner(
                $customerTable,
                $customerTable.'.entity_id = `main_table`.customer_id',
                $customerTable.'.email'
            );

		//echo $collection->getSelect()->__toString();   
        $this->setCollection($collection);
       
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('d1m_credits')->__('编号1'),
            'align'     =>'left',
            'width'     => '50px',
            'index'     => 'id'
        ));



        $this->addColumn('customer_email', array(
            'header'    => Mage::helper('d1m_credits')->__('客户emaail'),
            'align'     => 'left',
            'index'     => 'email',
            'filter_index'=>'email',

        ));

        $this->addColumn('credit_amount', array(
        		'header'    => Mage::helper('d1m_credits')->__('课点数'),
        		'align'     =>'left',
        		'index'     => 'credit_amount'
        ));
        
        
        
        $this->addColumn('created_at', array(
        		'header'    => Mage::helper('d1m_credits')->__('Created At'),
        		'align'     =>'left',
        		'type'		=> 'datetime',
        		'width'     => '100px',
        		'index'     => 'created_at'
        ));
        
        $this->addColumn('updated_at', array(
        		'header'    => Mage::helper('d1m_credits')->__('Updated At'),
        		'align'     =>'left',
        		'type'		=> 'datetime',
        		'width'     => '100px',
        		'index'     => 'updated_at'
        ));
        
   
        
        $this->addColumn('action',
        		array(
        				'header'    =>  Mage::helper('d1m_credits')->__('Action'),
        				'width'     => '100',
        				'type'      => 'action',
        				'getter'    => 'getId',
        				'actions'   => array(
        						array(
        								'caption'   => Mage::helper('d1m_credits')->__('Edit'),
        								'url'       => array('base' => '*/*/edit'),
        								'field'     => 'id',
        								'target'    => '_Blank'
        						)
        				),
        
        				'filter'    => false,
        				'sortable'  => false,
        				'index'     => 'stores',
        				'is_system' => true
        		));

        $this->addExportType('*/*/exportCsv/code/gbk', Mage::helper('d1m_credits')->__('CSV(中文)'));
		$this->addExportType('*/*/exportCsv', Mage::helper('d1m_credits')->__('CSV(utf8)'));
      	$this->addExportType('*/*/exportXml', Mage::helper('d1m_credits')->__('XML'));
		
        return parent::_prepareColumns();
    }
    
    
    
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('d1m_credits')->__('Delete'),
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
