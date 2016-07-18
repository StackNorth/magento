<?php

class D1m_Customapi_PosController extends Mage_Core_Controller_Front_Action
{
    //用户订单
    public function indexAction()
    {
        $mobile = $this->getRequest()->getParam('mobile', 0);
        $userId = 0;
        if ($mobile) {
            $customerModel = Mage::getModel('customer/customer');
            $exitingCollection = $customerModel->getCollection();//->addAttributeToSelect('username');
            $existingCollection = $exitingCollection->addFieldToFilter('phone', $mobile);
            if ($existingCollection->getSize() > 0) {
                $customer = $existingCollection->getFirstItem();
                $userId = $customer->getId();
            } else {
                $variables = array('status' => false, 'count' => 0, 'msg' => 'mobile does not exist', 'data' => '');
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($variables));
            }
        }

        $startTime = $this->getRequest()->getParam('start_time', 0);
        $endTime = $this->getRequest()->getParam('end_time', 0);
        $startTimeStr = '';
        $endTimeStr = '';
        if ($startTime && $endTime) {
            $startTimeStr = $startTime . ' 00:00:01';
            $endTimeStr = $endTime . ' 23:59:59';
        }
        if (($startTimeStr && $endTimeStr) or $userId) {
            $collection = Mage::Helper('customapi')->prepareCollection('', $startTimeStr, $endTimeStr, $userId, 0);
            $orderList = array();
            $courseTypes = Mage::Helper('customapi')->getCourseTypes();
            $citys = Mage::Helper('customapi')->getCity();
            $count = $collection->getSize();
        } else {
            $count = 0;
        }

        if ($count > 0) {
            foreach ($collection as $order) {
                //备注
                $orderInfo = $order->getData();

                $products = Mage::getModel('catalog/product')->load($orderInfo['product_id']);
                $couponRuleName = Mage::Helper('customapi')->getSalesRule($orderInfo['coupon_code']);
                $orderList[] = array(
                    'orderId' => $orderInfo['entity_id'],//订单ID
                    'customerId' => $orderInfo['customer_id'],//会员ID
                    'userName' => $orderInfo['username'],//姓名
                    'mobile' => $orderInfo['phone'],//手机号码
                    'courseType' => $courseTypes[$products->getCoursetype()],//课程类型
                    'courseCode' => $products->getSku(),//课程编码
                    'city' => $citys[$orderInfo['province']],//课程编码
                    'courseName' => $orderInfo['name'],//课程名称
                    'classDate' => $orderInfo['classdate'],//课程时间
                    'classStartTime' => $orderInfo['nclasstime1'],
                    'classEndTime' => $orderInfo['nclasstime2'],
                    'classAddress' => $orderInfo['class_address'],//课程地点,
                    'grandTotal' => $orderInfo['grand_total'],//实际支付金额,
                    'subtotal' => $orderInfo['subtotal'],// 收款金额
                    'peopleCount' => $orderInfo['item_count'],
                    'createdDate' => $orderInfo['created_at'],//购买时间
                    'order_payment_method' => $orderInfo['order_payment_method'],//付款方式
                    'couponCode' => $orderInfo['coupon_code'],//优惠码
                    'couponName' => $couponRuleName,//促销方案
                    'orderAdmin' => $orderInfo['order_admin'],//操作人员
                    'orderTrench' => $orderInfo['order_trench'],//购买渠道 购买地点 order_sign
                    'signIn' => $orderInfo['order_sign'],//是否签到
                    'sort_title' => $orderInfo['status']
                );

            }
        }
        $variables = array('status' => true, 'count' => $count, 'msg' => '', 'data' => $orderList);
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($variables));
    }
  //签到
    public function singAction()
    {
        $orderIds = $this->getRequest()->getParam('orderid', 0);
        $ids = explode(',', $orderIds);
        $msg = 'OK';
        $status = true;
        foreach ($ids as $id) {
            $orderModel = Mage::getModel('sales/order')->load($id);
            if ($orderModel->getId()) {
                $orderModel->setOrderSign(1);
                $now = Mage::getModel('core/date')->date('Y-m-d H:i:s');
                $orderModel->setUpdatedAt($orderModel->getUpdatedAt());
                $orderModel->save();
            } else {
                $msg = 'There is no orderId';
                $status = false;
            }
        }
        $variables = array('status' => $status, 'msg' => $msg, 'orderId' => $orderIds);
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($variables));
    }
//课点订单
    public function pointsAction()
    {
        // $customer_id=1 created_at

        $mobile = $this->getRequest()->getParam('mobile', 0);
        $customer_id = 0;
        if ($mobile) {
            $customerModel = Mage::getModel('customer/customer');
            $exitingCollection = $customerModel->getCollection();//->addAttributeToSelect('username');
            $existingCollection = $exitingCollection->addFieldToFilter('phone', $mobile);
            if ($existingCollection->getSize() > 0) {
                $customer = $existingCollection->getFirstItem();
                $customer_id = $customer->getId();
            } else {
                $variables = array('status' => false, 'count' => 0, 'msg' => 'mobile does not exist', 'data' => '');
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($variables));
            }
        }

        $startTime = $this->getRequest()->getParam('start_time', 0);
        $endTime = $this->getRequest()->getParam('end_time', 0);
        $startTimeStr = '';
        $endTimeStr = '';
        if ($startTime && $endTime) {
            $startTimeStr = $startTime . ' 00:00:01';
            $endTimeStr = $endTime . ' 23:59:59';
        }
        $collection = Mage::getResourceModel('d1m_credits/order_collection');
        $resource = Mage::getSingleton('core/resource');
        $customerTable = $resource->getTableName('customer_entity');
        $collection->getSelect()
            ->reset('columns')
            ->columns(array('id', 'status', 'qty', 'unit_price', 'gift_credits', 'grand_total', 'payment_method', 'created_at', 'customer_id', 'payment_method', 'order_type', 'order_from', 'order_trench'))
            ->joinInner(
                $customerTable,
                $customerTable . '.entity_id = `main_table`.customer_id',
                $customerTable . '.email'
            );
        //->where('main_table.status=?', 'complete');

        if ($customer_id) {
            $collection->getSelect()->where('main_table.customer_id=?', $customer_id);
        }
        if ($startTimeStr && $endTimeStr) {
            $collection->getSelect()->where('main_table.updated_at>?', $startTimeStr);
            $collection->getSelect()->where('main_table.updated_at<?', $endTimeStr);
        }
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
                    AND _table_customer_phone.attribute_id = ' . (int)$attrId,
                array('phone' => '_table_customer_phone.value'));

        $attr = $customertResource->getAttribute('username');
        $attrId = $attr->getAttributeId();
        $attrTable = $attr->getBackend()->getTable();
        $collection->getSelect()
            ->joinleft(
                array('_table_customer_username' => $attrTable),
                '_table_customer_username.entity_id=main_table.customer_id
                    AND (_table_customer_phone.entity_type_id=1)
                    AND _table_customer_username.attribute_id = ' . (int)$attrId,
                array('username' => '_table_customer_username.value'));
        $count = $collection->getSize();
        if ($count > 0) {
            foreach ($collection as $order) {
                //备注
                $orderInfo = $order->getData();


                $orderList[] = array(
                    'orderId' => $orderInfo['id'],//订单ID
                    'customerId' => $orderInfo['customer_id'],//会员ID
                    'userName' => $orderInfo['username'],//姓名
                    'mobile' => $orderInfo['phone'],//手机号码
                    'qty' => $orderInfo['qty'],//购买数量
                    'unit_price' => $orderInfo['unit_price'],//单价
                    'gift_credits' => $orderInfo['gift_credits'],//送数量
                    'order_payment_method' => $orderInfo['payment_method'],//付款方式
                    'grandTotal' => $orderInfo['grand_total'],//实际支付金额,
                    'createdDate' => $orderInfo['created_at'],//购买时间于
                    'order_from' => $orderInfo['order_from'],//来源
                    'orderTrench' => $orderInfo['order_trench'],//购买渠道 购买地点 order_sign
                    'order_type' => $orderInfo['order_type'],//是否签到
                    'sort_title' => $order['status']
                );

            }
        }
        $variables = array('status' => true, 'count' => $count, 'msg' => '', 'data' => $orderList);
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($variables));

    }

    //优惠券
    public function couponsAction()
    {

        //  $salesruleModel=   Mage::getResourceModel('salesrule/rule_collection');
        $salesruleModel = Mage::getModel('salesrule/rule');
        $name = urldecode($this->getRequest()->getParam('name', 'test'));
        $ruleInfo = $salesruleModel->load($name, 'name');
        if ($ruleInfo->getId()) {
            $couponCollection = Mage::getModel('salesrule/coupon')->getCollection();

            $rule_id = $ruleInfo->getId();
            $names = explode('|', $ruleInfo->getName());
            $price = $names[1] ? $names[1] . '.00' : '0.00';
            $collection = $couponCollection->addFieldToFilter('main_table.rule_id', $rule_id);

            $couponCollection->getSelect()->joinleft(
                array('lk' => Mage::getSingleton('core/resource')->getTableName('couponRule/coupon')),
                'lk.coupon = main_table.code',
                array('customer_email' => 'lk.customer_email')
            );

            $count = count($collection);
            $orderList = array();
            if ($count) {
                foreach ($collection as $item) {
                    $coupon = $item->getData();
                    $orderList[] = array(
                        //  'coupon_id' => $coupon['id'],// ID
                        'code' => $coupon['code'],//code
                        'created_at' => $coupon['created_at'],
                        'times_used' => $coupon['times_used'],
                        //  'usage_per_customer'=>$coupon['usage_per_customer'],//
                        'customer_email' => $coupon['customer_email'],
                        'price' => $price,
                    );
                }
            }

            $variables = array('status' => true, 'count' => $count, 'msg' => 'ok', 'data' => $orderList);
            header('Content-Type:application/json; charset=utf-8');
            exit(json_encode($variables));
        } else {
            $variables = array('status' => false, 'count' => 0, 'msg' => 'Could not find the data ', 'data' => '');
            header('Content-Type:application/json; charset=utf-8');
            exit(json_encode($variables));
        }


    }

    public function sandCardAction(){

        $file = $this->getRequest()->getParam('file', '');
        if($file){
            $sandCardModel=Mage::getModel('d1m_credits/sandcard');
            $content=$sandCardModel->import($file);
            $rows=explode("\r\n",$content);

            foreach($rows as $item){
                  $fields=  explode('|',$item);
                  $sandCardModel->saveRow($fields);
            }
            $variables = array('status' => true,  'msg' => 'save success');
        }else{
            $variables = array('status' => false,  'msg' => 'fiel not find');
        }

        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($variables));
    }

    public function testAction()
    {
         $id = $this->getRequest()->getParam('id', 506);
        $order = Mage::getModel('sales/order')->load($id);
        /* Mage::dispatchEvent('payment_accept_notify', array('order' => $order));*/

      //  $orderItemModel = Mage::getModel('sales/order_item')->getCollection();
       // $collection=$orderItemModel->addFieldToFilter('order_id',551);
      //  $date=$collection->getData();

    }

}