<?php
class D1m_Credits_Block_Adminhtml_Creditorder_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('creditorderGrid');
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
        /* @var $collection D1m_Credits_Model_Mysql4_Order_Collection */

        //email有冲突
        $collection = Mage::getResourceModel('d1m_credits/order_collection');
        $resource = Mage::getSingleton('core/resource');
        $customerTable = $resource->getTableName('customer_entity');
        $sandCardTable=$resource->getTableName('d1m_credits/sandcard');
        $collection->getSelect()
        ->reset('columns')
        ->columns(array('id','status','qty','unit_price','gift_credits','grand_total','payment_method','created_at','updated_at','payment_method','sand_card_number','financial_money'))
        ->joinInner(
            $customerTable,
            $customerTable.'.entity_id = `main_table`.customer_id',
            $customerTable.'.email'
        );
//echo $collection->getSelectSql(true);        die();

        $customertResource = Mage::getResourceSingleton('customer/customer');
        $attr = $customertResource->getAttribute('phone');
        $attrId = $attr->getAttributeId();
        $attrTable = $attr->getBackend()->getTable();
        //  die($attrTable);  aca_customer_entity_varchar
        $collection->getSelect()
        ->joinleft(
                array('_table_customer_phone' => $attrTable),
                '_table_customer_phone.entity_id=main_table.customer_id
                    AND (_table_customer_phone.entity_type_id=1)
                    AND _table_customer_phone.attribute_id = '.(int)$attrId,
                array('phone'=>'_table_customer_phone.value')
        )  //Sand卡信息
            ->joinLeft(array('sand'=>$sandCardTable),' sand.card_num=`main_table`.sand_card_number',
                array('sand_card_discount'=>'sand.discount'
                ))
        
                ;
        
        $attr = $customertResource->getAttribute('username');
        $attrId = $attr->getAttributeId();
        $attrTable = $attr->getBackend()->getTable();
        $collection->getSelect()
        ->joinleft(
                array('_table_customer_username' => $attrTable),
                '_table_customer_username.entity_id=main_table.customer_id
                    AND (_table_customer_phone.entity_type_id=1)
                    AND _table_customer_username.attribute_id = '.(int)$attrId,
                array('username'=>'_table_customer_username.value')        )
                ;

        $this->setCollection($collection);
       
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('d1m_credits')->__('编号'),
            'align'     =>'left',
            'width'     => '50px',
            'index'     => 'id',
        ));
        

        $statusOptions = Mage::helper('d1m_credits')->getCreditStatuses();
        
        $this->addColumn('status', array(
        		'header'    => Mage::helper('d1m_credits')->__('订单状态'),
        		'align'     => 'left',
        		'index'     => 'status',
        		'type'      => 'options',
        		'options'   => $statusOptions
        ));

        $resource = Mage::getSingleton('core/resource');
        $customerTable = $resource->getTableName('customer_entity');


        $this->addColumn('customer_email', array(
        		'header'    => Mage::helper('d1m_credits')->__('customer_email'),
        		'align'     => 'left',
        		'index'     =>'email',
                'filter_index'     => $customerTable.'.email',
        		//'type'      => 'options',
        		//'options'   => $dataArr
        ));
        $this->addColumn('username',array(
           'header'=>Mage::helper('sales')->__('用户名'),
           'type'=>'text',
           'index'=>'username',
           'filter_index'=>'`_table_customer_username`.`value`',
       ));
       $this->addColumn('phone',array(
           'header'=>Mage::helper('sales')->__('手机号'),
           'type'=>'text',
           'index'=>'phone',
           'filter_index'=>'`_table_customer_phone`.`value`',
       ));

        
        $this->addColumn('qty', array(
        		'header'    => Mage::helper('d1m_credits')->__('购买数量'),
        		'align'     =>'left',
        		'index'     => 'qty'
        ));

        $this->addColumn('gift_credits', array(
            'header'    => Mage::helper('d1m_credits')->__('赠送'),
            'align'     =>'left',
            'index'     => 'gift_credits'
        ));
        
        $this->addColumn('unit_price', array(
        		'header'    => Mage::helper('d1m_credits')->__('单价'),
        		'align'     =>'left',
        		'index'     => 'unit_price'
        ));


        $this->addColumn('grand_total', array(
        		'header'    => Mage::helper('d1m_credits')->__('金额'),
        		'align'     =>'left',
        		'index'     => 'grand_total'
        ));
        $this->addColumn('financial_money',
            array(
                'header'=> Mage::helper('catalog')->__('分摊金额'),
                'width' => '70px',
                'index' => 'sales_promotion',
                'type'  => 'text',

            ));
        $this->addColumn('sales_promotion',
            array(
                'header'=> Mage::helper('catalog')->__('促销方案'),
                'width' => '70px',
                'index' => 'sales_promotion',
                'type'  => 'text',

            ));
        $this->addColumn('sand_card_discount',
        array(
            'header'=> Mage::helper('catalog')->__('Sand折扣'),
            'width' => '100px',
            'index' => 'sand_card_discount',
            'type'  => 'text',

        ));
        $this->addColumn('sand_card_number',
            array(
                'header'=> Mage::helper('catalog')->__('Sand卡号'),
                'width' => '100px',
                'index' => 'sand_card_number',
                'type'  => 'text',

            ));



        $this->addColumn('payment_method', array(
            'header'    => Mage::helper('d1m_credits')->__('支付方式'),
            'align'     =>'left',
            'index'     => 'payment_method'
        ));




        $this->addColumn('created_at', array(
        		'header'    => Mage::helper('d1m_credits')->__('Created At'),
        		'align'     =>'left',
        		'type'		=> 'datetime',
        		'width'     => '100px',
        		'index'     => 'created_at',
                'filter_index' => 'main_table.created_at'
        ));
        
        $this->addColumn('updated_at', array(
        		'header'    => Mage::helper('d1m_credits')->__('Updated At'),
        		'align'     =>'left',
        		'type'		=> 'datetime',
        		'width'     => '100px',
        		'index'     => 'updated_at',
                'filter_index' => 'main_table.updated_at'
        ));
        
   
      /*
        $this->addColumn('action',
        		array(
        				'header'    =>  Mage::helper('d1m_credits')->__('Action'),
        				'width'     => '100',
        				'type'      => 'action',
        				'getter'    => 'getId',
        				'actions'   => array(
        						array(
        								'caption'   => Mage::helper('d1m_credits')->__('查看'),
        								'url'       => array('base' => 'edit'),
        								'field'     => 'id',
        								'target'    => '_Blank'
        						)
        				),
        
        				'filter'    => false,
        				'sortable'  => false,
        				'index'     => 'stores',
        				'is_system' => true
        		));*/
        $this->addColumn('setMoney',
            array(
                'header'    => Mage::helper('sales')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('sales')->__('设分摊金额'),
                        'url'     => array('base'=>'*/report/creditsorderMoney'),
                        'field'   => 'order_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
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
