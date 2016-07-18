<?php
class D1m_Adminhtml_Block_Sales_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'sales/order_grid_collection';
    }

    protected function _prepareCollection()
    {

        /*  $collection Mage_Sales_Model_Resource_Order_Grid_Collection */
        //$collection = Mage::getResourceModel($this->_getCollectionClass());

        /* @var $collection D1m_Adminhtml_Model_Sales_Order_Grid_Collection */
        $collection=Mage::getModel('d1m_adminhtml/sales_order_grid_collection');
        $collection->addAttributeToSelect('*');
//查看不同的attribute....

        // Mage_Sales_Model_Resource_Order_Grid_Collection echo get_class($collection);die();
        $collection->setD1mSpecial(true); //需要重写计数

        //注意表前缀，应取配置resources/db/table_prefix
        //$xml = simplexml_load_file('./app/etc/local.xml', NULL, LIBXML_NOCDATA);
        //$pref = $xml->global->resources->db->table_prefix;

        $resource = Mage::getSingleton('core/resource');
        $itemTable = $resource->getTableName('sales_flat_order_item');
        $paymentTable=$resource->getTableName('sales_flat_order_payment');
        $orderTable=$resource->getTableName('sales_flat_order');

        $sandCardTable=$resource->getTableName('d1m_credits/sandcard');

        $collection->getSelect()->join($itemTable,$itemTable.'.order_id = `main_table`.entity_id',
            array(' sum('.$itemTable.'.qty_ordered) as item_count','max('.$itemTable.'.product_id) as product_id','max('.$itemTable.'.name) as name'));
        $collection->getSelect()->group('main_table.entity_id');
        //echo $collection->getSelect();        die();

        //SELECT `main_table`.*, sum(qty_ordered) AS `ocount` FROM `aca_sales_flat_order_grid` AS `main_table` INNER JOIN `aca_sales_flat_order_item` ON aca_sales_flat_order_item.order_id = `main_table`.entity_id GROUP BY `main_table`.`entity_id`


        //product_id
        //customer_id
        //上课日期 ，上课时间，  课程名，城市，地点，
        //用户名，手机号

        $productResource = Mage::getResourceSingleton('catalog/product');


        $classdateAttr = $productResource->getAttribute('class_date');
        $classdateAttrId = $classdateAttr->getAttributeId();
        $classdateAttrTable = $classdateAttr->getBackend()->getTable();


        $nclasstime1Attr = $productResource->getAttribute('n_classtime1');
        $nclasstime1AttrId = $nclasstime1Attr->getAttributeId();
        $nclasstime1AttrTable = $nclasstime1Attr->getBackend()->getTable();

        $nclasstime2Attr = $productResource->getAttribute('n_classtime2');
        $nclasstime2AttrId = $nclasstime2Attr->getAttributeId();
        $nclasstime2AttrTable = $nclasstime2Attr->getBackend()->getTable();


        $classprovinceAttr = $productResource->getAttribute('province');
        $classprovinceAttrId = $classprovinceAttr->getAttributeId();
        $classprovinceAttrTable = $classprovinceAttr->getBackend()->getTable();

//店铺视图
        $classaddressAttr = $productResource->getAttribute('class_address');
        $classaddressAttrId = $classaddressAttr->getAttributeId();
        $classaddressAttrTable = $classaddressAttr->getBackend()->getTable();

       // $salesruleModel=   Mage::getResourceModel('salesrule/rule_collection');
      //  $salesruleModel->addAttribtoselect('rule_id,name');



        $couponTable      = $resource->getTableName('salesrule/coupon');


        $storeId = Mage::app()->getStore()->getId();


        $collection->getSelect()
            ->joinleft(
                array('_table_product_classdate' => $classdateAttrTable),
                '_table_product_classdate.entity_id='.$itemTable.'.product_id
                    AND (_table_product_classdate.store_id = 0)
                    AND _table_product_classdate.attribute_id = '.(int)$classdateAttrId,
                array('classdate'=>'_table_product_classdate.value')        )
            ->joinleft(
                array('_table_product_nclasstime1' => $nclasstime1AttrTable),
                '_table_product_nclasstime1.entity_id='.$itemTable.'.product_id
                    AND (_table_product_nclasstime1.store_id = 0)
                    AND _table_product_nclasstime1.attribute_id = '.(int)$nclasstime1AttrId,
                array('nclasstime1'=>'_table_product_nclasstime1.value')        )
            ->joinleft(
                array('_table_product_nclasstime2' => $nclasstime2AttrTable),
                '_table_product_nclasstime2.entity_id='.$itemTable.'.product_id
                    AND (_table_product_nclasstime2.store_id = 0)
                    AND _table_product_nclasstime2.attribute_id = '.(int)$nclasstime2AttrId,
                array('nclasstime2'=>'_table_product_nclasstime2.value')        )
//地址以admin为准,不考虑店铺视图
            ->joinleft(
                array('_table_product_classaddress' => $classaddressAttrTable),
                '_table_product_classaddress.entity_id='.$itemTable.'.product_id
                    AND (_table_product_classaddress.store_id = 0)
                    AND _table_product_classaddress.attribute_id = '.(int)$classaddressAttrId,
                array('class_address'=>'_table_product_classaddress.value')        )
//省份取的是编号
            ->joinleft(
                array('_table_product_classprovince' => $classprovinceAttrTable),
                '_table_product_classprovince.entity_id='.$itemTable.'.product_id
                    AND (_table_product_classprovince.store_id = 0)
                    AND _table_product_classprovince.attribute_id = '.(int)$classprovinceAttrId,
                array('province'=>'_table_product_classprovince.value')        )
            //支付方式
            ->join(array('sfop'=>$paymentTable),' sfop.parent_id=main_table.entity_id',
                array('order_payment_method'=>'sfop.method'
                ))


            // 课点支付,订单金额
            ->join(array('sfo'=>$orderTable),' sfo.entity_id=main_table.entity_id',
                array('credit_amount'=>'sfo.credit_amount',
                    'subtotal'=>'sfo.subtotal',
                    'grandtotal2'=>'sfo.grand_total',
                    'coupon_code'=>'sfo.coupon_code',
                    'order_from'=>'sfo.order_from',
                    'financial_money'=>'sfo.financial_money',
                    'sand_card_number'=>'sfo.sand_card_number',
                ))
            //Sand卡信息
            ->joinLeft(array('sand'=>$sandCardTable),' sand.card_num=sfo.sand_card_number',
                array('sand_card_discount'=>'sand.discount'
                ))
            ->joinleft(array('coup'=>$couponTable),' coup.code=sfo.coupon_code',
                array('rule_id'=>'coup.rule_id'
                ))/**/

        ;
//产品名称

     //   Mage::getResourceModel('couponRule/coupon_collection');
       // $tableName      = Mage::getSingleton('core/resource')->getTableName('salesrule/coupon');


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
                array('phone'=>'_table_customer_phone.value')        )

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
        /* @var $customertResource D1m_Customer_Model_Resource_Customer */
        $collection->getSelect()
        ->joinleft(
                array('_table_customer_email' => $customertResource->getEntityTable()),
                '_table_customer_email.entity_id=main_table.customer_id',
                array('email'=>'_table_customer_email.email')        )
                ;

        // echo $collection->getSelectSql();        die();
/*

            ->from("",array(
                    'product_lassaddress2' => "_table_product_lassaddress2.value",
                    'product_lassaddress1' => "_table_product_lassaddress.value",
                    //'product_lassaddress' => new Zend_Db_Expr('IFNULL(_table_product_lassaddress.value,_table_product_lassaddress2.value)')
                    'product_lassaddress' => new Zend_Db_Expr('IF(_table_product_lassaddress.value_id>0,_table_product_lassaddress.value,_table_product_lassaddress2.value)')
                )
            )

        ;
*/

//未使用joinAttribute


        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
   {


       $model=Mage::getResourceModel('catalog/product_collection');
       $attr=$model->getAttribute('province');
       $optionss = $attr->getSource()->getAllOptions();
       //var_dump($options);
       // array(3) {[0]=> array(2) { ["label"]=> string(0) "" ["value"]=> string(0) "" } [1]=> array(2) { ["value"]=> string(2) "12" ["label"]=> string(6) "上海" } [2]=> array(2) { ["value"]=> string(2) "19" ["label"]=> string(6) "北京" } }
       $arrCityoptions=array();
       $PaymentList=Mage::getModel('d1m_adminhtml/sales_order_grid_collection')->getPaymentList();
      
       foreach($optionss as $item)
       {
           $label=$item['label'];
           $value=$item['value'];
           if ($value=="") continue;
           $arrCityoptions[$value]=$label;
       }

       $salesruleConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
       $salesruleTable =  Mage::getSingleton('core/resource')->getTableName('salesrule/rule');
       $salesRuleSelect = $salesruleConnection->select()
           ->from($salesruleTable, array('rule_id','name'));
       $salesRuleList = $salesruleConnection->fetchAll($salesRuleSelect); // 返回所以行
       $salesRules=array();
       foreach($salesRuleList as $rule){
           $salesRules[$rule['rule_id']]=$rule['name'];
       }
       //var_dump($arrCityoptions);
       //$arrCityoptions=array(''=>'','12'=>'上海','19'=>'北京');
       //var_dump($arrCityoptions);
//       die();
        $this->addColumn('province',array
        (
            'header'=>Mage::helper('sales')->__('城市'),
            'type'=>'options',
            'index'=>'province',
            'width' => '60px',
            'filter_index'=>'_table_product_classprovince.value',
            'options' =>$arrCityoptions,

        ));


        $this->addColumn('real_order_id', array(
            'header'=> Mage::helper('sales')->__('Order #'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'increment_id',
            'filter_index'=>'main_table.increment_id',
        ));


       /*
        if (!Mage::app()->isSingleStoreMode())
        {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('sales')->__('Purchased From (Store)'),
                'index'     => 'store_id',
				'filter_index' => 'main_table.store_id',
                'type'      => 'store',
                'store_view'=> true,
                'display_deleted' => true,
            ));
        }
       */

        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('创建日期'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
			'filter_index' => 'main_table.created_at',
            'filter_condition_callback'=>array($this, '_createdatFilter'),
        ));

        /*$this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'index' => 'billing_name',
        ));*/

       /*
        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'shipping_name',
        ));


       $this->addColumn('country_id', array(
            'header'=> Mage::helper('sales')->__('Delivery Country'),
            'type'  => 'country',
            'index' => 'country_id',
			'width'=>'50px',
			'filter_index'=>'sales_flat_order_address.country_id',
        ));
       */

       $this->addColumn('name',array
       (
           'header'=>Mage::helper('sales')->__('课程名称'),
           'type'=>'text',
           'index'=>'name',
           'filter_index'=>'name',
       ));


       $this->addColumn('class_address',array
       (
           'header'=>Mage::helper('sales')->__('上课地址'),
           'type'=>'text',
           'index'=>'class_address',
           'filter_index'=>'`_table_product_classaddress`.`value`',


       ));
       $this->addColumn('class_date',array
       (
           'header'=>Mage::helper('sales')->__('上课日期'),
           'type'=>'date',
           'index'=>'classdate',
           'filter_index'=>'_table_product_classdate.value',
           'filter_condition_callback'=>array($this, '_classdateFilter'),
       ));
       $this->addColumn('n_clastime1',array
       (
           'header'=>Mage::helper('sales')->__('开始时间'),
           'type'=>'text',
           'index'=>'nclasstime1',
           'filter_index'=>'_table_product_nclasstime1.value',
       ));
       $this->addColumn('n_clastime2',array
       (
           'header'=>Mage::helper('sales')->__('结束时间'),
           'type'=>'text',
           'index'=>'nclasstime2',
           'filter_index'=>'_table_product_nclasstime2.value',
       ));

       $this->addColumn('username',array
       (
           'header'=>Mage::helper('sales')->__('用户名'),
           'type'=>'text',
           'index'=>'username',
           'filter_index'=>'`_table_customer_username`.`value`',
       ));
       $this->addColumn('phone',array
       (
           'header'=>Mage::helper('sales')->__('手机号'),
           'type'=>'text',
           'index'=>'phone',
           'filter_index'=>'`_table_customer_phone`.`value`',
       ));
       $this->addColumn('email',array
       (
           'header'=>Mage::helper('sales')->__('邮箱'),
           'type'=>'text',
           'index'=>'email',
           'filter_index'=>'`_table_customer_email`.`email`',
       ));
       $this->addColumn('coupon_code', array(
           'header' => '优惠券',
           'index' => 'coupon_code',
           'type'=>'text',
           'filter_index' => 'sfo.coupon_code',
       ));
        $this->addColumn('rule_id',
            array(
                'header'=> Mage::helper('catalog')->__('促销方案'),
                'width' => '70px',
                'index' => 'rule_id',
                'type'=>'options',
                'options' => $salesRules

            ));
       $this->addColumn('item_count',array
       (
           'header'=>Mage::helper('sales')->__('人数'),
           'type'=>'number',
           'index'=>'item_count',
           'width'=>'50',
           'filter_index'=>'item_count',
           'filter_condition_callback'=>array($this, '_salesQuantityFilter'),
       ));






/*
       $this->addColumn('base_grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Base)'),
            'index' => 'base_grand_total',
            'type'  => 'currency',
            'currency' => 'base_currency_code',
			'filter_index'=>'main_table.base_grand_total',
        ));
*/


        $this->addColumn('subtotal', array(
            'header' => '金额',
            'index' => 'subtotal',
            'type'  => 'currency',
            //  'currency' => 'order_currency_code',
            'filter_index' => 'subtotal',
        ));

        $this->addColumn('credit_amount', array(
            'header' => '课点支付',
            'index' => 'credit_amount',
            'type'  => 'currency',
         //   'currency' => 'order_currency_code',
            'filter_index' => 'sfo.credit_amount',
        ));
        /*
        $this->addColumn('grand_total', array(
            'header' => '应付金额',
            'index' => 'grand_total',
            'type'  => 'currency',
            //  'currency' => 'order_currency_code',
            'filter_index' => 'grand_total',
        ));
        */

    /**/    $this->addColumn('total_paid', array(
            'header' => '实际付款额',
            'index' => 'total_paid',//total_paid
             'type'  => 'currency',
            //  'currency' => 'order_currency_code',
             'filter_index' =>'sfo.total_paid',
        ));
        $this->addColumn('financial_money',
            array(
                'header'=> Mage::helper('catalog')->__('分摊金额'),
                'width' => '70px',
                'index' => 'financial_money',
                'type'  => 'text',

            ));
        $this->addColumn('sand_card_discount',
            array(
                'header'=> Mage::helper('catalog')->__('Sand卡折扣'),
                'width' => '40px',
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
        $this->addColumn('financial_money',
            array(
                'header'=> Mage::helper('catalog')->__('分摊金额'),
                'width' => '70px',
                'index' => 'financial_money',
                'type'  => 'text',

            ));

        $this->addColumn('order_payment_method', array(
            'header' => Mage::helper('sales')->__('付款方式'),
            'index' => 'order_payment_method',
            'filter_index'=>'sfop.method',
            /*'type'  => 'text',*/
           'type'=>'options',
             'options'=>$PaymentList
        ));


        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
			'filter_index'=>'main_table.status',
            'type'  => 'options',
            'width' => '150px',
           // 'options' => Mage::getSingleton('sales_order_grid_collection')->getStatuses(),
            'options' => Mage::getModel('d1m_adminhtml/sales_order_grid_collection')->getStatuses(),

        ));
        $this->addColumn('order_from', array(
            'header' => Mage::helper('sales')->__('订单来源'),
            'index' => 'order_from',
            'filter_index'=>'sfo.order_from',

            'type'=>'options',
             'options'=>array(
                 'web'=>"官网" ,
                 'store'=>"门店",
                 'weixin'=>"微信"
             )
        ));
		$this->addColumn('updated_at',array
				(
					'header'=>Mage::helper('sales')->__('更新日期'),
					'type'=>'datetime',
					'index'=>'updated_at',
					'width'=>'100px',
					'filter_index' => 'main_table.updated_at',
					'filter_condition_callback'=>array($this, '_updatedatFilter'),
				)
		);
		
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $this->addColumn('action',
                array(
                    'header'    => Mage::helper('sales')->__('Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getId',
                    'actions'   => array(
                        array(
                            'caption' => Mage::helper('sales')->__('View'),
                            'url'     => array('base'=>'*/sales_order/view'),
                            'field'   => 'order_id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
            ));
        }
        $this->addColumn('setMoney',
            array(
                'header'    => Mage::helper('sales')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('sales')->__('设分摊金额'),
                        'url'     => array('base'=>'*/report/setOrderMoney'),
                        'field'   => 'order_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
            ));
        $this->addRssList('rss/order/new', Mage::helper('sales')->__('New Order RSS'));


        $this->addExportType('*/*/exportCsv/code/gbk', Mage::helper('sales')->__('CSV(中文)'));
        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV(utf-8)'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel XML'));


        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('order_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/cancel')) {
            $this->getMassactionBlock()->addItem('cancel_order', array(
                 'label'=> Mage::helper('sales')->__('Cancel'),
                 'url'  => $this->getUrl('*/sales_order/massCancel'),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/hold')) {
            $this->getMassactionBlock()->addItem('hold_order', array(
                 'label'=> Mage::helper('sales')->__('Hold'),
                 'url'  => $this->getUrl('*/sales_order/massHold'),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/unhold')) {
            $this->getMassactionBlock()->addItem('unhold_order', array(
                 'label'=> Mage::helper('sales')->__('Unhold'),
                 'url'  => $this->getUrl('*/sales_order/massUnhold'),
            ));
        }

        $this->getMassactionBlock()->addItem('pdfinvoices_order', array(
             'label'=> Mage::helper('sales')->__('Print Invoices'),
             'url'  => $this->getUrl('*/sales_order/pdfinvoices'),
        ));

        $this->getMassactionBlock()->addItem('pdfshipments_order', array(
             'label'=> Mage::helper('sales')->__('Print Packingslips'),
             'url'  => $this->getUrl('*/sales_order/pdfshipments'),
        ));

        $this->getMassactionBlock()->addItem('pdfcreditmemos_order', array(
             'label'=> Mage::helper('sales')->__('Print Credit Memos'),
             'url'  => $this->getUrl('*/sales_order/pdfcreditmemos'),
        ));

        $this->getMassactionBlock()->addItem('pdfdocs_order', array(
             'label'=> Mage::helper('sales')->__('Print All'),
             'url'  => $this->getUrl('*/sales_order/pdfdocs'),
        ));

        $this->getMassactionBlock()->addItem('print_shipping_label', array(
             'label'=> Mage::helper('sales')->__('Print Shipping Labels'),
             'url'  => $this->getUrl('*/sales_order_shipment/massPrintShippingLabel'),
        ));

        return $this;
    }

    public function getRowUrl($row)
    {
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            return $this->getUrl('*/sales_order/view', array('order_id' => $row->getId()));
        }
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    protected function _createdatFilter($collection, $column)
	{
		if(!$value = $column->getFilter()->getValue())
		{
			return $this;
		}
		
		$from = $value['from'];
		$to = $value['to'];
		if(!is_null($from))
		{
            $date=Mage::app()->getLocale()->date($from);
            $str= $date->toString('yyyy-MM-dd 00:00:00');
			$this->getCollection()->getSelect()->where('main_table.created_at >= ?',$str);
		}
		if(!is_null($to))
		{
            $date=Mage::app()->getLocale()->date($to);
            $str= $date->toString('yyyy-MM-dd 23:59:59');
			$this->getCollection()->getSelect()->where('main_table.created_at <= ?', $str);
		}

		return $this;
	}

    protected function _updatedatFilter($collection, $column)
    {
        if(!$value = $column->getFilter()->getValue())
        {
            return $this;
        }

        $from = $value['from'];
        $to = $value['to'];
        //2014-11-26 下午04:00:00
        if(!is_null($from))
        {
            $date=Mage::app()->getLocale()->date($from);
            $str= $date->toString('yyyy-MM-dd 00:00:00');
            $this->getCollection()->getSelect()->where('main_table.updated_at >= ?',$str);
        }
        if(!is_null($to))
        {
            $date=Mage::app()->getLocale()->date($to);
            $str= $date->toString('yyyy-MM-dd 23:59:59');
            $this->getCollection()->getSelect()->where('main_table.updated_at <= ?', $str);
        }
        return $this;
    }



    protected function _salesQuantityFilter($collection, $column)
	{
		if(!$value = $column->getFilter()->getValue())
		{
			return $this;
		}
        $from = $value['from'];
        $to = $value['to'];
		if(!is_null($from))
		{
            $this->getCollection()->getSelect()->having('SUM(qty_ordered)>=?',$from);
		}
		if(!is_null($to))
		{
            $this->getCollection()->getSelect()->having('SUM(qty_ordered)<=?',$to);
		}
		return $this;
	}

    protected function _classdateFilter($collection, $column)
    {
        if(!$value = $column->getFilter()->getValue())
        {
            return $this;
        }

        $from = $value['from'];
        $to = $value['to'];
        //2014-11-26 下午04:00:00
        //_table_product_classdate.value
        if(!is_null($from))
        {

            $date=Mage::app()->getLocale()->date($from);
            $str= $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
            $this->getCollection()->getSelect()->where('_table_product_classdate.value >= ?',$str);
        }
        if(!is_null($to))
        {
            $date=Mage::app()->getLocale()->date($to);
            $str= $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
            $this->getCollection()->getSelect()->where('_table_product_classdate.value <= ?', $str);
        }
        //die($this->getCollection()->getSelectSql());

        return $this;
    }




    protected function _setCollectionOrder($column)
	{
		if ($column->getOrderCallback())
        {
			call_user_func($column->getOrderCallback(), $this->getCollection(), $column);

			return $this;
		}

		return parent::_setCollectionOrder($column);
	}
}
