<?php
class D1m_WeiXinAPI_Model_Order_Api extends Mage_Api_Model_Resource_Abstract{


    protected $_quote;
    protected $_paymentCode = 'alipay_payment';

    /**
     * 订单查询
     * @param  $data
     * @return array
     * @throws Mage_Api_Exception
     */
    public function queryOrders($data){

        if(!$data['pagesize']){
            $data['pagesize'] = '10';
        }
        if(!$data['curpage']){
            $data['curpage'] = '1';
        }
        $result = $this->_validateData2($data);
        if(isset($result['code']) && !$result['code']){
            return $result ;
        }
        $pagesize = $data['pagesize'];
        $curpage = $data['curpage'];

        $orders = array();
        try{
            /* @var $collection D1m_Adminhtml_Model_Sales_Order_Grid_Collection */
            $collection=Mage::getModel('d1m_adminhtml/sales_order_grid_collection');
            $collection->addAttributeToSelect('*');

            $collection->setD1mSpecial(true); //需要重写计数
            $resource = Mage::getSingleton('core/resource');
            $itemTable = $resource->getTableName('sales_flat_order_item');
            $paymentTable=$resource->getTableName('sales_flat_order_payment');
            $orderTable=$resource->getTableName('sales_flat_order');

            $collection->getSelect()->join($itemTable,$itemTable.'.order_id = `main_table`.entity_id',
                array(' sum('.$itemTable.'.qty_ordered) as item_count','max('.$itemTable.'.product_id) as product_id','max('.$itemTable.'.name) as name'));
            $collection->getSelect()->group('main_table.entity_id');

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

            $classshopAttr = $productResource->getAttribute('n_shop');
            $classshopAttrId = $classshopAttr->getAttributeId();
            $classshopAttrTable = $classshopAttr->getBackend()->getTable();


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
                    array(
                        //'classdate'=>'_table_product_classdate.value'
                    )        )
                ->joinleft(
                    array('_table_product_nclasstime1' => $nclasstime1AttrTable),
                    '_table_product_nclasstime1.entity_id='.$itemTable.'.product_id
                    AND (_table_product_nclasstime1.store_id = 0)
                    AND _table_product_nclasstime1.attribute_id = '.(int)$nclasstime1AttrId,
                    array(
                        //'nclasstime1'=>'_table_product_nclasstime1.value'
                    )        )
                ->joinleft(
                    array('_table_product_nclasstime2' => $nclasstime2AttrTable),
                    '_table_product_nclasstime2.entity_id='.$itemTable.'.product_id
                    AND (_table_product_nclasstime2.store_id = 0)
                    AND _table_product_nclasstime2.attribute_id = '.(int)$nclasstime2AttrId,
                    array(
                        //'nclasstime2'=>'_table_product_nclasstime2.value'
                    )        )

//地址以admin为准,不考虑店铺视图
                ->joinleft(
                    array('_table_product_classaddress' => $classaddressAttrTable),
                    '_table_product_classaddress.entity_id='.$itemTable.'.product_id
                    AND (_table_product_classaddress.store_id = 0)
                    AND _table_product_classaddress.attribute_id = '.(int)$classaddressAttrId,
                    array(
                        //'class_address'=>'_table_product_classaddress.value'
                    ))
//省份取的是编号
                ->joinleft(
                    array('_table_product_classprovince' => $classprovinceAttrTable),
                    '_table_product_classprovince.entity_id='.$itemTable.'.product_id
                    AND (_table_product_classprovince.store_id = 0)
                    AND _table_product_classprovince.attribute_id = '.(int)$classprovinceAttrId,
                    array(
                        //'province'=>'_table_product_classprovince.value'
                    ))

                //获得门店
                ->joinleft(
                    array('_table_product_classshop' => $classshopAttrTable),
                    '_table_product_classshop.entity_id='.$itemTable.'.product_id
                    AND (_table_product_classshop.store_id = 0)
                    AND _table_product_classshop.attribute_id = '.(int)$classshopAttrId,
                    array(
                        //'n_shop'=>'_table_product_classshop.value'
                    ))

                //支付方式
                ->join(array('sfop'=>$paymentTable),' sfop.parent_id=main_table.entity_id',
                    array('order_payment_method'=>'sfop.method'
                    ))
                // 课点支付,订单金额
                ->join(array('sfo'=>$orderTable),' sfo.entity_id=main_table.entity_id',
                    array('credit_amount'=>'sfo.credit_amount',
                        'subtotal'=>'sfo.subtotal',
                        //'grandtotal2'=>'sfo.grand_total',
                        //'coupon_code'=>'sfo.coupon_code',
                       // 'order_from'=>'sfo.order_from',
                    ));

            //产品名称
            $customertResource = Mage::getResourceSingleton('customer/customer');
            $attr = $customertResource->getAttribute('phone');
            $attrId = $attr->getAttributeId();
            $attrTable = $attr->getBackend()->getTable();

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
                    array(
                    //'email'=>'_table_customer_email.email'
                    ));
            if($data['n_shop']){
                $collection->getSelect()->where("_table_product_classshop.value={$data['n_shop']}");
            }
            //分页
            $collection->setPageSize($pagesize)->setCurPage($curpage);
            //return $collection->fetchItem() ;
            $i = 0;
            foreach ($collection as $order) {

                $orders[$i]['entity_id'] = $order->getEntityId();
                $orders[$i]['n_shop'] = $order->getNShop();
                $orders[$i]['customer_id'] = $order->getCustomerId();
                $orders[$i]['created_at'] = $order->getCreatedAt();
                $orders[$i]['username'] = $order->getUsername();
                $orders[$i]['phone'] = $order->getPhone();
                $orders[$i]['subtotal'] = $order->getSubtotal();
                $orders[$i]['item_count'] = $order->getItemCount();
                $orders[$i]['total_paid'] = $order->getTotalPaid();
                $orders[$i]['status'] = $order->getStatus();
                $i++;
            }

            $pagenum = $collection->getLastPageNumber();
            return array('code'=>1,'data'=>$orders,'pagenum'=>$pagenum);
        }catch (Mage_Core_Exception $e){
            return array('code'=>0,'message'=>$e->getMessage());
        }

    }


    public function updateOrder($data){

        if(!$data['orderId']){
            return array('code'=>0,'message'=>'订单id不能为空');
        }
        if($data['credit_num'] && !is_numeric($data['credit_num'])){
            return array('code'=>0,'message'=>'课点数必须为数字类型');
        }
        if($data['amount'] && !is_numeric($data['amount'])){
            return array('code'=>0,'message'=>'支付金额必须为数字类型');
        }

        if(!empty($data['coupon_code']) && !$data['ruleText']){
            return array('code'=>0,'message'=>'优惠券和促销方案必须同时存在');
        }
        if(!$data['coupon_code'] && !empty($data['ruleText'])){
            return array('code'=>0,'message'=>'优惠券和促销方案必须同时存在');
        }

        $order = Mage::getModel('sales/order')->load($data['orderId']);

        if($order->getId()){
//            $coupon = Mage::getModel('salesrule/coupon');
//            $coupon->load($data['coupon_code'],'code');
            if(!empty($data['ruleText'])){

                $rule = Mage::getModel('salesrule/rule');
                $collection = $rule->getCollection();
                $collection->addFieldToFilter('name',array('like'=>'%'.trim($data['ruleText']).'%'));
                if($collection->getSize()>1){
                    return array('code'=>0,'message'=>'促销方案不唯一');
                }
                $rule = $collection->getFirstItem();
            }
            try{
                $ruleId = '';
                if(isset($rule)){
                    $ruleId = $rule->getId();
                }
                $order->setData('coupon_code',$data['coupon_code']);
                $order->setData('applied_rule_ids',$ruleId);
                $order->setData('credit_amount',$data['credit_num']*(-1));
                $order->setData('total_paid',$data['amount']);
                $order->setData('state',Mage_Sales_Model_Order::STATE_COMPLETE);
                $order->setData('status',Mage_Sales_Model_Order::STATE_COMPLETE);
                $order->save();

                $crmOrderApi = Mage::getModel('customapi/orderservice');
                $crmOrderApi->addClassOrder($order);


                if($data['amount']>0){
                    $payMethod = 'weChat_payment';
                }else{
                    $payMethod = '';
                }
                /** @var $payment Mage_Sales_Model_Order_Payment */
                $payment = Mage::getModel('sales/order_payment');

                $payment->load($data['orderId']);
                $payment->setData('method',$payMethod);
                $payment->save();

                $credit = Mage::getModel('d1m_credits/credits');
                $credit->load($order->getCustomerId(),'customer_id');

                $credit->setData('credit_amount',($credit->getCreditAmount()-$data['credit_num']));
                $credit->save();

                return array('code'=>1,'message'=>'订单更新成功');

            }catch (Mage_Core_Exception $e){
                return array('code'=>0,'message'=>$e->getMessage());
            }

        }else{
            return array('code'=>0,'message'=>'找不到此订单');
        }
    }



    /**
     * 生成订单
     * @param $data
     * @return mix
     */
    public function createOrder($data){
        $result = $this->_validateData($data);
        if(isset($result['code']) && !$result['code']){
            return $result ;
        }
        $product = $this->initProduct($data);

        if (!$product->getId()){
            return array('code'=>0,'message'=>'无法找到产品');
        }
        $customer = $this->_getCustomer($data['billing']['email']);
        if(!$customer){
            return array('code'=>0,'message'=>'无法找到用户') ;
        }

        /** @var $helper D1m_WeiXinAPI_Helper_Data */
        $helper = Mage::helper('wxapi/data');
        $address = $helper->validateAddress($data['billing']);
        if(isset($address['code']) && !$address['code']){
            return $address ;
        }

        return $this->saveOrder($product,$data['qty'],$customer,$address);

    }

    /**
     * 取消订单
     * @param $data
     * @return array
     */
    public function cancelOrder($data){

        $order = Mage::getModel('sales/order')->load($data['orderId']);

        $cc = $order->canCancel();
        if(!$cc){
            Mage::log($order->getId().'==',null,'weixinorder.log');
            return array('code'=>0,'message'=>'订单取消失败');
        }
        $order->cancel();
        $order->save();

        return array('code'=>1,'message'=>'订单取消成功');
    }
    protected function saveOrder(Mage_Catalog_Model_Product $product,$qty,$customer,$address){

        try{
            $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
            if($stock->getQty()<$qty){
                return array('code' => 0,'message' =>'库存不足！');
            }
            $shippingAddress = $this->getQuote()->getShippingAddress();
            $billingAddress  = $this->getQuote()->getBillingAddress();
            $shippingAddress->addData($address);
            $billingAddress->addData($address);
            $shippingAddress->setEmail($customer->getEmail());
            $this->getQuote()->assignCustomer($customer);
            $qty = (int)$qty;
            while ($qty>0){
                $this->getQuote()->addProduct($product,new Varien_Object());
                $qty--;
            }

            $this->getQuote()->getPayment()->importData(array('method' => $this->_paymentCode));
            //$this->getQuote()->setCouponCode('SHC1412TPDE2A0');

            //return $this->getQuote()->getAppliedRuleIds();
            /* @var $service Mage_Sales_Model_Service_Quote */
            $service = Mage::getModel('sales/service_quote', $this->getQuote());

            $service->submitAll();

            $this->getQuote()->save();

            $order = $service->getOrder();
            if ($order && $order->getId())
            {
                $order->getCustomerId();
                $order->setData('order_from','weixin')->save();
                return array('code' => 1, 'data'=>$order->getId(),'message' =>'订单同步成功');
            }
        }catch (Mage_Core_Exception $e){
            Mage::logException($e);
            return array('code' => 0, 'message' =>$e->getMessage());
        }

    }

    protected function _getCustomer($email){
        /** @var  $customerModel Mage_Customer_Model_Customer */
        $customerModel =  Mage::getModel('customer/customer');
        $customer = $customerModel->setStore(Mage::app()->getStore(2))
                                    ->loadByEmail($email);
        return $customer ;
    }
    /**
     * 获得商品实体
     * @param $data
     * @return mix
     */
    protected function initProduct($data) {
        $sku = $data['sku'];

        $product = Mage::getModel('catalog/product')->setStoreId(2);
        $product = $product->load($product->getIdBySku($sku));

        return $product ;
    }

    /***
     * 获得quote
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function getQuote()
    {
        if (is_null($this->_quote))
        {
            $this->_quote = Mage::getModel('sales/quote')
                            ->setStoreId(2);
        }
        return $this->_quote;
    }

    protected function initAddress(){
        /* @var $address Mage_Customer_Model_Address */
        $address =  Mage::getModel('customer/address');
        /* @var $addressForm Mage_Customer_Model_Form */
        $addressForm    = Mage::getModel('customer/form');
        $addressForm->setFormCode('customer_address_edit')
            ->setEntityType('customer_address');

        $addressForm->setEntity($address);
    }

    /**
     * 检查订单数据
     * @param $data
     * @return bool
     */
    protected function _validateData($data)
    {
        if (!isset($data['sku']))
        {
            return array('code'=>0,'message'=>'产品SKU不能为空');
        }

        if (!isset($data['qty']) || !ctype_digit($data['qty']))
        {
            return array('code'=>0,'message'=>'产品数量不能为空或者非数字');
        }

        return ;
    }

    /**
     * 验证分页数据
     * @param $data
     * @return array|void
     */
    protected function _validateData2($data){
        if(!isset($data['pagesize']) || !ctype_digit($data['pagesize']) || $data['pagesize']<1){
            return array('code'=>0,'message'=>'每页显示数量不能为空并且是大于1的数字');
        }
        if(!isset($data['curpage']) || !ctype_digit($data['curpage']) || $data['curpage']<1){
            return array('code'=>0,'message'=>'当前页数不能为空并且是大于1的数字');
        }
        if(isset($data['n_shop']) && !ctype_digit($data['n_shop'])){
            return array('code'=>0,'message'=>'门店代码必须是数字');
        }
        return ;
    }

}