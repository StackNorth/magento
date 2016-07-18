<?php
class D1m_Customapi_Helper_Data extends Mage_Core_Helper_Abstract
{	
	
	public static $erpClient = null;
	
	const XML_NODE_ERP_URL         = 'global/erp_url';
	
	public function getGenerateUniqueId($length = 10)
	{
		$rndId = crypt(uniqid(rand(),1));
        $rndId = strip_tags(stripslashes($rndId));
        $rndId = str_replace(array(".", "$"),"",$rndId);
        $rndId = strrev(str_replace("/","",$rndId));
        if (!is_null($rndId)){
        	return strtoupper(substr($rndId, 0, $length));
        }
        return strtoupper($rndId);
		
	}
	
	public function getSoapUrl()
	{
		$configUrl =  Mage::getConfig()->getNode(self::XML_NODE_ERP_URL);
		$configUrl =  $configUrl ? (string)$configUrl : self::getErpSoapUrl();
		return $configUrl;
	}
	
	public function getSoapClient()
	{
		if(is_null(self::$erpClient))
		{
			$configUrl =  self::getSoapUrl();
			self::$erpClient = new SoapClient($configUrl,array('trace' => true));
		}
		
		return self::$erpClient;
	}
    public function getSalesRule($couponCode){
        if($couponCode){
            $ruleModel=  Mage::getModel('salesrule/rule');
            $couponModel=Mage::getModel('salesrule/coupon')->load($couponCode,'code');
            $ruleInfo= $ruleModel->load($couponModel->getRuleId());
            return $ruleInfo->getName();
        }
       return '';


    }
    public function getCourseTypes(){
        $attribute_code = 'coursetype';
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product',$attribute_code);
        $options = $attribute->getSource()->getAllOptions();
        foreach ($options as $option)
        {
            $label=$option['label'];
            $value=$option['value'];
            if ($value=="") continue;
            $types[$value]=$label;
        }
        return $types;
    }
    public function getCity(){
        $model=Mage::getResourceModel('catalog/product_collection');
        $attr=$model->getAttribute('province');
        $optionss = $attr->getSource()->getAllOptions();
        $arrCityoptions=array();
        foreach($optionss as $item)
        {
            $label=$item['label'];
            $value=$item['value'];
            if ($value=="") continue;
            $arrCityoptions[$value]=$label;
        }
        return $arrCityoptions;
    }
    public  function prepareCollection($id=0,$startTimeStr='',$endTimeStr='',$customer_id=0,$status=true)
    {



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

        $collection->getSelect()->join($itemTable,$itemTable.'.order_id = `main_table`.entity_id',
            array(' sum('.$itemTable.'.qty_ordered) as item_count','max('.$itemTable.'.product_id) as product_id','max('.$itemTable.'.name) as name'));
        $collection->getSelect()->group('main_table.entity_id');
      //  $collection->addAttributeToFilter('`sfo`.order_from',  'store');
       //  $collection->addAttributeToFilter('`sfo`.order_trench',  'order_trench');
       // $collection->addAttributeToFilter('`sfo`.order_admin',  'order_admin');
        if($status){
            $collection->addAttributeToFilter('`main_table`.status',  'complete');
        }

        if($id) {
            $collection->addAttributeToFilter('`main_table`.`entity_id`', $id);
        }elseif($startTimeStr && $endTimeStr){
            $collection->addAttributeToFilter('`main_table`.`updated_at`',  array('gteq' => $startTimeStr));
            $collection->addAttributeToFilter('`main_table`.`updated_at`',  array('lteq' => $endTimeStr));
        }

        if($customer_id){
            $collection->addAttributeToFilter('`main_table`.`customer_id`', $customer_id);
        }


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
            // 课点支付,订单金额 签到
            ->join(array('sfo'=>$orderTable),' sfo.entity_id=main_table.entity_id',
                array('credit_amount'=>'sfo.credit_amount',
                    'subtotal'=>'sfo.subtotal',
                    'grandtotal2'=>'sfo.grand_total',
                    'coupon_code'=>'sfo.coupon_code',
                    'order_from'=>'sfo.order_from',
                    'order_trench'=>'sfo.order_trench',
                    'order_admin'=>'sfo.order_admin',
                    'order_sign'=>'sfo.order_sign'
                ))


        ;
//产品名称

     //echo $collection->getSelect();        die();
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


//未使用joinAttribute


        return $collection;//->getFirstItem();

    }


}
