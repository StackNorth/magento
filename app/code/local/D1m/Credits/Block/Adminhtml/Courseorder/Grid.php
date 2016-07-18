<?php
class D1m_Credits_Block_Adminhtml_Courseorder_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('courseorderGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }
    
    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {

       		$resource = Mage::getSingleton('core/resource');
        	$order_table = $resource->getTableName('sales/order');
            $customer_table = $resource->getTableName('customer_entity');
        	
        	$_productCollection = Mage::getModel('sales/order_item')
        								->getCollection();
        	
        	$productResource = Mage::getResourceSingleton('catalog/product');
	        $classdateAttr = $productResource->getAttribute('class_date');
	        $classdateAttrId = $classdateAttr->getAttributeId();
	        $classdateAttrTable = $classdateAttr->getBackend()->getTable();
	        
	        
	        $classaddressAttr = $productResource->getAttribute('class_address');
	        $classaddressAttrId = $classaddressAttr->getAttributeId();
	        $classaddressAttrTable = $classaddressAttr->getBackend()->getTable();
	        
	        $statusAttr = $productResource->getAttribute('status');
	        $statusAttrId = $statusAttr->getAttributeId();
	        $statusAttrTable = $statusAttr->getBackend()->getTable();
        	
	        $nclasstime1Attr = $productResource->getAttribute('n_classtime1');
	        $nclasstime1AttrId = $nclasstime1Attr->getAttributeId();
	        $nclasstime1AttrTable = $nclasstime1Attr->getBackend()->getTable();

            $nclasstime2Attr = $productResource->getAttribute('n_classtime2');
            $nclasstime2AttrId = $nclasstime2Attr->getAttributeId();
            $nclasstime2AttrTable = $nclasstime2Attr->getBackend()->getTable();


        	
        	$storeId = Mage::app()->getStore()->getId();
//注意属性 global 店铺视图 不同处理
        	$_productCollection->getSelect()

                ->joinInner(
								                array('_order' => $order_table),
								                '_order.entity_id=main_table.order_id',
								               array('order_increment_id'=>'_order.increment_id',
														'order_status'=>'_order.status',
														'grand_total'=>'_order.grand_total',
														'contact_info'=>'_order.contact_info',
														'customer_id'=>'_order.customer_id',
                                               ))

                ->joinInner(
                    array('_customer'=>$customer_table),
                    '_order.customer_id=_customer.entity_id',
                    'email'
                )

								            ->joinleft(
									                array('_table_product_classdate' => $classdateAttrTable),
									                '_table_product_classdate.entity_id=main_table.product_id
									                    AND (_table_product_classdate.store_id = 0)
									                    AND _table_product_classdate.attribute_id = '.(int)$classdateAttrId,
									               array('classdate'=>'_table_product_classdate.value')        )
											->joinleft(
								                array('_table_product_nclasstime1' => $nclasstime1AttrTable),
								                '_table_product_nclasstime1.entity_id=main_table.product_id
								                AND (_table_product_nclasstime1.store_id = 0)
								                    AND _table_product_nclasstime1.attribute_id = '.(int)$nclasstime1AttrId,
								               array('n_classtime1'=>'_table_product_nclasstime1.value')                )
                                            ->joinleft(
								                array('_table_product_nclasstime2' => $nclasstime2AttrTable),
                                                '_table_product_nclasstime2.entity_id=main_table.product_id
                                                AND (_table_product_nclasstime2.store_id = 0)
                                        AND _table_product_nclasstime2.attribute_id = '.(int)$nclasstime2AttrId,
                                                array('n_classtime2'=>'_table_product_nclasstime2.value')          )

								            ->joinleft(
								                array('_table_product_lassaddress' => $classaddressAttrTable),
								                '_table_product_lassaddress.entity_id=main_table.product_id 
								                    AND (_table_product_lassaddress.store_id = '.$storeId.')
								                    AND _table_product_lassaddress.attribute_id = '.(int)$classaddressAttrId, 
								                        array('product_store_id'=>'_table_product_lassaddress.store_id')	    )
                                        ->joinleft(
								                array('_table_product_lassaddress2' => $classaddressAttrTable),
								                '_table_product_lassaddress2.entity_id = main_table.product_id
								                    AND (_table_product_lassaddress2.store_id = 0)
								                    AND _table_product_lassaddress2.attribute_id = '.(int)$classaddressAttrId, 
								                 array('')
								            )


								            ->from("",array(
								                        'product_lassaddress2' => "_table_product_lassaddress2.value",
								                        'product_lassaddress1' => "_table_product_lassaddress.value",
								                        // 'product_lassaddress' => new Zend_Db_Expr('IFNULL(_table_product_lassaddress.value,_table_product_lassaddress2.value)')
                        'product_lassaddress' => new Zend_Db_Expr('IF(_table_product_lassaddress.value_id>0,_table_product_lassaddress.value,_table_product_lassaddress2.value)')

								                        )
        									)
            
            ;

//              echo $_productCollection->getSelect(); die();
        	
        	$collection = $_productCollection;
       
       
       
        $this->setCollection($collection);
       
        return parent::_prepareCollection();
    }
    
    protected function _addColumnFilterToCollection($column)
    {
    	
        if ($column->getId() == 'customer_id') {
            
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('_order.customer_id', array('eq'=>$column->getFilter()->getValue()));
            }
            
        }
        elseif ($column->getId() == 'status') {
            
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('_order.status', array('eq'=>$column->getFilter()->getValue()));
            }
            
        }
        elseif ($column->getId() == 'classdate') {
        	
        	$values = $column->getFilter()->getValue();
        	
        	if(isset($values['from']))
        		$fromDate = $values['from']->setTime('00:00:00')->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        	else
        		$fromDate = null;
        		
        	if(isset($values['to']))
        		$toDate = $values['to']->setTime('23:59:59')->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        	else
        		$toDate = null;
        	
        	if($fromDate)
        	{
        		$this->getCollection()->addFieldToFilter('_table_product_classdate.value', array('gteq'=>$fromDate));
        	}
        	
        	if($toDate)
        	{
        		$this->getCollection()->addFieldToFilter('_table_product_classdate.value', array('lteq'=>$fromDate));
        	}
        	
        }
        else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }
    
    
    protected function _prepareColumns()
    {

        $this->addColumn('id', array(
            'header'    => Mage::helper('d1m_credits')->__('ID'),
            'align'     =>'left',
            'width'     => '50px',
            'index'     => 'order_id'
        ));

        /*
         $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('username')
            ->addAttributeToSelect('email');
        $dataArr = array();
        foreach($collection as $customer)
        {
            $dataArr[$customer->getId()] = $customer->getUsername().'['.$customer->getEmail().']';
        }
        */
        
        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'order_status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));

        $resource = Mage::getSingleton('core/resource');
        $customerTable = $resource->getTableName('customer_entity');
        
        $this->addColumn('customer_email', array(
        		'header'    => Mage::helper('d1m_credits')->__('客户email'),
        		'align'     => 'left',
        		'filter_index'     => 'email',
            'index'     => 'email',
                //'filter_index'     => $customerTable.'.email',

        		//'type'      => 'options',
        		//'options'   => $dataArr
        ));
        
        $this->addColumn('qty', array(
        		'header'    => Mage::helper('d1m_credits')->__('数量'),
        		'align'     =>'left',
        		'index'     => 'qty_ordered'
        ));
        
        $this->addColumn('sku', array(
        		'header'    => Mage::helper('d1m_credits')->__('sku'),
        		'align'     =>'left',
        		'filter'    => false,
        		'index'     => 'sku'
        ));
        
        $this->addColumn('name', array(
        		'header'    => Mage::helper('d1m_credits')->__('名称'),
        		'align'     =>'left',
        		'filter'    => false,
        		'index'     => 'name'
        ));
        
        $this->addColumn('product_lassaddress', array(
        		'header'    => Mage::helper('d1m_credits')->__('上课地址'),
        		'align'     =>'left',
        		'filter'    => false,
        		'index'     => 'product_lassaddress'
        ));
        
        $this->addColumn('classdate', array(
        		'header'    => Mage::helper('d1m_credits')->__('上课日期'),
        		'align'     =>'left',
        		'index'     => 'classdate',
        		'type'		=> 'date',
        		'width'     => '100px',
        ));


        $this->addColumn('nclasstimea', array(
            'header'    => Mage::helper('d1m_credits')->__('开始时间'),
            'align'     =>'left',
            'filter'    => false,
            'index'     => 'n_classtime1'
        ));
        $this->addColumn('nclasstimeb', array(
            'header'    => Mage::helper('d1m_credits')->__('结束时间'),
            'align'     =>'left',
            'filter'    => false,
            'index'     => 'n_classtime2'
        ));

        /*$options = Mage::getResourceSingleton('catalog/product')->getAttribute('?')->getSource()->getAllOptions();
        $arrayOptions = array();
        foreach($options as $option)
        {
        	$arrayOptions[$option['value']] = $option['label'];
        }
        $this->addColumn('classsection', array(
        		'header'    => Mage::helper('d1m_credits')->__('?'),
        		'align'     => 'left',
        		'index'     => 'classsection',
        		'type'      => 'options',
        		'filter'    => false,
        		'options'   => $arrayOptions
        ));
        */
        
        $this->addColumn('contact_info', array(
        		'header'    => Mage::helper('d1m_credits')->__('联络人'),
        		'align'     =>'left',
        		'filter'    => false,
        		'index'     => 'contact_info'
        ));
        
        
        $this->addColumn('grand_total', array(
        		'header'    => Mage::helper('d1m_credits')->__('金额'),
        		'align'     =>'left',
        		'filter'    => false,
        		'index'     => 'grand_total'
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
        								'caption'   => Mage::helper('d1m_credits')->__('查看'),
        								'url'       => array('base' => '*/sales_order/view'),
        								'field'     => 'order_id',
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
        return $this->getUrl('*/sales_order/view', array('order_id' => $row->getOrderId()));
    }
    


    

}
