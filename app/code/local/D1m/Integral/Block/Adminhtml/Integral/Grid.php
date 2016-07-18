<?php
class D1m_Integral_Block_Adminhtml_Integral_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('integralGrid');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }
    
    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('d1m_integral/integral_collection')
			        ;

		//echo $collection->getSelect()->__toString();   
        $this->setCollection($collection);
       
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('d1m_integral')->__('ID'),
            'align'     =>'left',
            'width'     => '50px',
            'index'     => 'id'
        ));
        
         $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('username')
            ->addAttributeToSelect('email');
        $dataArr = array();
        foreach($collection as $customer)
        {
            $dataArr[$customer->getId()] = $customer->getUsername().'['.$customer->getEmail().']';
        }
        
        $this->addColumn('customer_id', array(
        		'header'    => Mage::helper('d1m_integral')->__('customer'),
        		'align'     => 'left',
        		'index'     => 'customer_id',
        		'type'      => 'options',
        		'options'   => $dataArr
        ));
        
        $this->addColumn('credit_amount', array(
        		'header'    => Mage::helper('d1m_integral')->__('Integral amount'),
        		'align'     =>'left',
        		'index'     => 'credit_amount'
        ));
        
        
        
        $this->addColumn('created_at', array(
        		'header'    => Mage::helper('d1m_integral')->__('Created At'),
        		'align'     =>'left',
        		'type'		=> 'datetime',
        		'width'     => '100px',
        		'index'     => 'created_at'
        ));
        
        $this->addColumn('updated_at', array(
        		'header'    => Mage::helper('d1m_integral')->__('Updated At'),
        		'align'     =>'left',
        		'type'		=> 'datetime',
        		'width'     => '100px',
        		'index'     => 'updated_at'
        ));
        
   
        
        $this->addColumn('action',
        		array(
        				'header'    =>  Mage::helper('d1m_integral')->__('Action'),
        				'width'     => '100',
        				'type'      => 'action',
        				'getter'    => 'getId',
        				'actions'   => array(
        						array(
        								'caption'   => Mage::helper('d1m_integral')->__('Edit'),
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
        
		$this->addExportType('*/*/exportCsv', Mage::helper('d1m_integral')->__('CSV'));
      	$this->addExportType('*/*/exportXml', Mage::helper('d1m_integral')->__('XML'));
		
        return parent::_prepareColumns();
    }
    
    
    
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('d1m_integral')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('d1m_integral')->__('Are you sure?')
        ));

       
        return $this;
    }
    
    
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
    


    

}
