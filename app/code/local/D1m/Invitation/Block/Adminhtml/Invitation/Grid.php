<?php
class D1m_Invitation_Block_Adminhtml_Invitation_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('invitationGrid');
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
        $collection = Mage::getResourceModel('d1m_invitation/invitation_collection')
			        ;

		//echo $collection->getSelect()->__toString();   
        $this->setCollection($collection);
       
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('d1m_invitation')->__('ID'),
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
        		'header'    => Mage::helper('d1m_invitation')->__('customer'),
        		'align'     => 'left',
        		'index'     => 'customer_id',
        		'type'      => 'options',
        		'options'   => $dataArr
        ));
        
        $this->addColumn('status', array(
            'header'    => Mage::helper('d1m_invitation')->__('Status'),
            'index'     => 'status',
            'type'      => 'options',
            'options'   => array(
                0 => Mage::helper('d1m_invitation')->__('Pending'),
                1 => Mage::helper('d1m_invitation')->__('Registered')
            ),
        ));
        
        $this->addColumn('coupon_code', array(
        		'header'    => Mage::helper('d1m_invitation')->__('Coupon Code'),
        		'align'     =>'left',
        		'index'     => 'coupon_code'
        ));
        
        $this->addColumn('name', array(
        		'header'    => Mage::helper('d1m_invitation')->__('name'),
        		'align'     =>'left',
        		'index'     => 'name'
        ));
        
        $this->addColumn('email', array(
        		'header'    => Mage::helper('d1m_invitation')->__('email'),
        		'align'     =>'left',
        		'index'     => 'email'
        ));
        
        $this->addColumn('phone', array(
        		'header'    => Mage::helper('d1m_invitation')->__('phone'),
        		'align'     =>'left',
        		'index'     => 'phone'
        ));
        
        $this->addColumn('note', array(
        		'header'    => Mage::helper('d1m_invitation')->__('note'),
        		'align'     =>'left',
        		'index'     => 'note'
        ));
        
        $this->addColumn('created_at', array(
        		'header'    => Mage::helper('d1m_invitation')->__('Created At'),
        		'align'     =>'left',
        		'type'		=> 'datetime',
        		'width'     => '100px',
        		'index'     => 'created_at'
        ));
        
        $this->addColumn('updated_at', array(
        		'header'    => Mage::helper('d1m_invitation')->__('Updated At'),
        		'align'     =>'left',
        		'type'		=> 'datetime',
        		'width'     => '100px',
        		'index'     => 'updated_at'
        ));
        
   
        
        $this->addColumn('action',
        		array(
        				'header'    =>  Mage::helper('d1m_invitation')->__('Action'),
        				'width'     => '100',
        				'type'      => 'action',
        				'getter'    => 'getId',
        				'actions'   => array(
        						array(
        								'caption'   => Mage::helper('d1m_invitation')->__('Edit'),
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
        
		$this->addExportType('*/*/exportCsv', Mage::helper('d1m_invitation')->__('CSV'));
      	$this->addExportType('*/*/exportXml', Mage::helper('d1m_invitation')->__('XML'));
		
        return parent::_prepareColumns();
    }
    
    
    
    protected function _prepareMassaction()
    {
        return $this;
    }
    
    
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
    


    

}
