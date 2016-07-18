<?php

/**
 * User: ahsw@qq.com
 * caeate Time: 2016/5/413:45
 */
class D1m_Credits_Adminhtml_ReportController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('etam/producttool')
            ->_addBreadcrumb($this->__('D1m Slides'), $this->__('Report'));
        return $this;
    }

    public function setOrderMoneyAction()
    {

        $orderId = (int)$this->getRequest()->getParam('order_id');
        if ($postData = $this->getRequest()->getPost()) {
            try {
                $order = Mage::getModel('sales/order')->load($orderId);
                $financial_money = $postData['money'];
                $resource = Mage::getSingleton('core/resource');
                $tableName = $resource->getTableName('sales/order');
                $writeConnection = $resource->getConnection('core_write');
                $query = "UPDATE `{$tableName}` SET `financial_money` ='{$financial_money}' WHERE entity_id=" . $orderId;
                $writeConnection->query($query);


                $this->_redirect('*/sales_order/index', array('_current' => true));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->setRecordData($order->getData());
                $this->_redirect('*/sales_order/index', array('_current' => true));
            }
        } else {
            $this->_initAction();
            $order = Mage::getModel('sales/order')->load($orderId);


            Mage::register('current_order', $order);

            $this->_title($this->__('设置订单价格'));
            $this->_addContent($this->getLayout()->createBlock('d1m_credits/adminhtml_order_edit'));
            $this->renderLayout();

        }

    }

    public function creditsorderMoneyAction()
    {
        $orderId = (int)$this->getRequest()->getParam('order_id');
        if ($postData = $this->getRequest()->getPost()) {

            try {
                //   $order =  Mage::getModel('d1m_credits/order')->load($orderId);
                $financial_money = $postData['money'];
                $resource = Mage::getSingleton('core/resource');
                $tableName = $resource->getTableName('d1m_credits/order');
                $writeConnection = $resource->getConnection('core_write');
                $query = "UPDATE `{$tableName}` SET `financial_money` ='{$financial_money}' WHERE id=" . $orderId;
                $writeConnection->query($query);


                $this->_redirect('*/creditsorder/index', array('_current' => true));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                //   $this->_getSession()->setRecordData($order->getData());
                $this->_redirect('*/creditsorder/index', array('_current' => true));
            }
        } else {
            $this->_initAction();
            $order = Mage::getModel('d1m_credits/order')->load($orderId);
            Mage::register('current_order', $order);
            
            $this->_title($this->__('设置订单价格'));
            $this->_addContent($this->getLayout()->createBlock('d1m_credits/adminhtml_order_edit'));
            $this->renderLayout();

        }

    }

    //课程订单
    public function courseOrderAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $startTimeStr = trim($data['begin_time']) . ' 00:00:01';
            $endTimeStr = trim($data['end_time']) . ' 23:59:59';
            $collection = Mage::Helper('customapi')->prepareCollection('', $startTimeStr, $endTimeStr, 0);
            $count = $collection->getSize();
            if ($count > 0) {
                $orderList = '订单ID,用户名,手机,订单来源,上课地点,购买类别,购买日期,收款方式,实际付款金额' . "\r\n";
                foreach ($collection as $order) {
                    $orderInfo = $order->getData();
                    if ($orderInfo['grand_total'] < 1) {
                        $orderInfo['order_payment_method'] = '-';
                    }
                    $item = array(
                        $orderInfo['entity_id'],//订单ID
                        $orderInfo['username'],//姓名
                        $orderInfo['phone'],//手机号码
                        $orderInfo['order_from'],//订单来源
                        $orderInfo['class_address'],//课程地点,
                        '课程',//课程地点,
                        $orderInfo['created_at'],//购买时间
                        $orderInfo['order_payment_method'],//付款方式
                        $orderInfo['grand_total'],//实际支付金额,
                    );
                    $orderList .= implode(',', $item) . "\r\n";
                }
                $this->_sendUploadResponse('courseOrder.csv', $orderList);
            } else {
                exit('order id null');
            }
        } else {
            $this->_initAction();
            $this->_title($this->__('导出课程订单'));
            $this->_addContent($this->getLayout()->createBlock('d1m_credits/adminhtml_report_edit'));
            $this->renderLayout();
        }

    }

    //课点订单收款

    public function creditsOrderAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $startTimeStr = trim($data['begin_time']) . ' 00:00:01';
            $endTimeStr = trim($data['end_time']) . ' 23:59:59';
            $collection = Mage::getResourceModel('d1m_credits/order_collection');
            $resource = Mage::getSingleton('core/resource');
            $customerTable = $resource->getTableName('customer_entity');
            $customertResource = Mage::getResourceSingleton('customer/customer');

            $collection->getSelect()->where('`main_table`.status =?', 'complete');
            $collection->getSelect()->where('`main_table`.`created_at`>?', $startTimeStr);
            $collection->getSelect()->where('`main_table`.`created_at`<?', $endTimeStr);
            // ->reset('columns')
            // ->columns(array('id','status','qty','unit_price','gift_credits','grand_total','payment_method','created_at','updated_at','payment_method'))
            $collection->getSelect()
                ->joinInner(
                    $customerTable,
                    $customerTable . '.entity_id = `main_table`.customer_id',
                    $customerTable . '.email'
                );
            $attr = $customertResource->getAttribute('username');
            $attrId = $attr->getAttributeId();
            $attrTable = $attr->getBackend()->getTable();
            $collection->getSelect()
                ->joinleft(
                    array('_table_customer_username' => $attrTable),
                    '_table_customer_username.entity_id=main_table.customer_id
                    AND _table_customer_username.attribute_id = ' . (int)$attrId,
                    array('username' => '_table_customer_username.value')
                );

            // echo $collection->getSelect()->__toString();die;
            $count = $collection->getSize();
            if ($count > 0) {
                $orderList = '订单ID,用户名,订单来源,订单类型,购买门店,购买类别,购买日期,收款方式,实际付款金额' . "\r\n";
                foreach ($collection as $order) {
                    $orderInfo = $order->getData();
                    if ($orderInfo['grand_total'] < 1) {
                        $orderInfo['order_payment_method'] = '-';
                    }
                    $item = array(
                        $orderInfo['id'],//订单ID
                        $orderInfo['username'],//姓名
                        $orderInfo['order_from'],//订单来源
                        $orderInfo['order_type'],//订单类型,
                        $orderInfo['order_trench'],//购买地点,
                        '课点',//购买类别
                        $orderInfo['created_at'],//购买时间
                        $orderInfo['payment_method'],//付款方式
                        $orderInfo['grand_total'],//实际支付金额,
                    );
                    $orderList .= implode(',', $item) . "\r\n";
                }
                $this->_sendUploadResponse('creditsOrder.csv', $orderList);
                exit;
                //   echo $collection->getSelect()->__toString();die;
            } else {
                exit('order id null');
            }
        } else {
            $this->_initAction();
            $this->_title($this->__('导出课程订单'));
            $this->_addContent($this->getLayout()->createBlock('d1m_credits/adminhtml_report_edit'));
            $this->renderLayout();
        }


    }

    //课程课点使用明细
    public function singOrderAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $startTimeStr = trim($data['begin_time']) . ' 00:00:01';
            $endTimeStr = trim($data['end_time']) . ' 23:59:59';
            $collection = Mage::Helper('customapi')->prepareCollection('', $startTimeStr, $endTimeStr, 0);
            $count = $collection->getSize();
            if ($count > 0) {
                $orderList = '订单ID,用户名,手机,使用日期,课程名称,课程价格,使用店铺,原收款方式' . "\r\n";

                foreach ($collection as $order) {
                    $orderInfo = $order->getData();

                    if ($orderInfo['grand_total'] < 1) {
                        $orderInfo['order_payment_method'] = '-';
                    }
                    if ($orderInfo['order_sign'] == 0) {
                        $orderInfo['updated_at'] = '-';
                    }
                    $item = array(
                        $orderInfo['entity_id'],//订单ID
                        $orderInfo['username'],//姓名
                        $orderInfo['phone'],//手机号码
                        $orderInfo['updated_at'],//使用日期
                        $orderInfo['name'],//课程名称
                        $orderInfo['financial_money'],//课程价格
                        $orderInfo['class_address'],//课程地点,
                        $orderInfo['order_payment_method'],//付款方式

                    );
                    $orderList .= implode(',', $item) . "\r\n";
                }
                $this->_sendUploadResponse('signOrder.csv', $orderList);
            } else {
                exit('order id null');
            }
        } else {
            $this->_initAction();
            $this->_title($this->__('导出课程使用明细'));
            $this->_addContent($this->getLayout()->createBlock('d1m_credits/adminhtml_report_edit'));
            $this->renderLayout();
        }

    }

    //月度余额
    public function monthOrderAction()
    {

        $resource = Mage::getSingleton('core/resource');


        $customertTable = $resource->getTableName('customer_entity');
        $customertResource = Mage::getResourceSingleton('customer/customer');
        $month = Mage::getModel('core/date')->gmtDate('Y-m-01');
        $balanceList = Mage::getModel('d1m_credits/balance')->getCollection()->addFieldToFilter('`main_table`.created_date', $month);
        $balanceList->getSelect()->join(
            array('customer_email' => $customertTable),
            '(customer_email.entity_id=main_table.uid)',
            array('email' => 'customer_email.email'
            ));
        $attr = $customertResource->getAttribute('phone');
        $attrId = $attr->getAttributeId();
        $attrTable = $attr->getBackend()->getTable();
        $balanceList->getSelect()
            ->joinleft(
                array('customer_phone' => $attrTable),
                '(customer_phone.entity_id=main_table.uid
                    AND (customer_phone.entity_type_id=1)
                    AND customer_phone.attribute_id = ' . (int)$attrId.')',
                array('phone' => 'customer_phone.value')
            );
       // echo $balanceList->getSelect();
       // die(';ok');

        $count = count($balanceList);

        if ($count > 0) {

            $currMonth = Mage::getModel('core/date')->gmtDate('Y-m-01');
            $date  = new  DateTime ( $currMonth);
            $date -> modify ( '-1 month' );
            $lastMonth=  $date -> format ('Y-m-d H:i:s');
            $currMonthDayNum = date('t', strtotime($lastMonth));
            $date -> modify ( '+'.$currMonthDayNum.' day' );
            $lastMonthOver=  $date -> format ('Y-m-d H:i:s');


            $csvContentString="用户ID,邮箱,手机,上月余额,月收入金额,月使用金额,当月余额\r\n";
            foreach ($balanceList as $item) {
                $orderEarning=$orderUse=0;
                $orderModel = Mage::getModel('sales/order')->getCollection();
                $orderModel->addFieldToFilter('customer_id',$item->getUid());
                $orderModel->addFieldToFilter('updated_at', array('from' => $lastMonth, 'to' => $lastMonthOver));


               $orderModel->getSelect()->where( "`status` IN('complete','refund')" )->reset('columns')
                    ->columns(array('status','financial_money','created_at','updated_at'));
               // echo $orderModel->getSelect();
               //  die(';ok');
              // $len= count($orderModel);

                foreach($orderModel as $order){

          //  echo $order->getStatus();
                    if($order->getStatus()=='complete'){
                         $orderEarning+=$order->getFinancialMoney();
                    }else{
                       $createDate= substr($order->getCreatedAt(),0,7);
                       $updateDate= substr($order->getUpdatedAt(),0,7);
                        if($createDate!=$updateDate){
                            $orderEarning-=$order->getFinancialMoney();
                        }

                    }

                }
                $orderModel = Mage::getModel('sales/order')->getCollection();
                $orderModel->addFieldToFilter('customer_id',$item->getUid());
                $orderModel->addFieldToFilter('updated_at', array('from' => $lastMonth, 'to' => $lastMonthOver));

               $useOrderList= $orderModel->getSelect()->where( "`status` ='complete' AND order_sign='1'" )->reset('columns') ->columns(array('status','financial_money'));
                foreach($useOrderList as $order){
                        $orderUse += $order->getFinancialMoney();
                }
                $customerCredit=   Mage::getModel('d1m_credits/order')->getCustomerCredits($item->getUid(),$lastMonth,$lastMonthOver);
                $upMonthMoney = $item->getOrderMoney() + $item->getCredits();
                $item = array(
                    'uid'=>$item->getUid(),
                    'email' => $item->getEmail(),
                    'phone' => $item->getPhone(),
                    'up_month_money' => $upMonthMoney,
                    'month_earning_money'=>$orderEarning+$customerCredit,//收入
                  //  'month_earning_credits'=>$customerCredit,
                    'month_use_money'=>$orderUse,//使用
                   // 'month_use_credits'=>0,
                    'month_balance'=>$upMonthMoney//余额
                );
                $csvContentString .= implode(',', $item) . "\r\n";
            }

              $this->_sendUploadResponse('monthOrder.csv',$csvContentString);
        }else{
            exit('data is null .Please run   "cron/d1m_balance.php"');
        }





    }

    public function exportCsvAction()
    {
        //$fileName   = 'OutOfStock.csv';
        //$content    = $this->getLayout()->createBlock('cg_lowStock/adminhtml_Catalog_Product_OutOfStock_grid')
        //   ->getCsv();
        // $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream')
    {
        $content = iconv("UTF-8", "GBK", $content);
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        exit();
    }

    /**
     * Cancel order
     */
    public function cancelOrderAction()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);


        if ($order) {
            try {
                $order->setState('canceled')
                    ->setStatus('canceled')
                    ->save();
                Mage::getModel('d1m_adminhtml/sales_order_grid_collection')->updateOrderStatus($id, 'canceled');
                $this->_getSession()->addSuccess(
                    $this->__('The order has been cancelled.')
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('The order has not been cancelled.'));
                Mage::logException($e);
            }
            $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
        }
    }

    /**
     * refund order
     */
    public function reversedOrderAction()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);
        if ($order) {
            try {
                $order->save();
                Mage::getModel('d1m_adminhtml/sales_order_grid_collection')->updateOrderStatus($id, 'refund');
                $this->_getSession()->addSuccess(
                    $this->__('The order has been  reversed.')
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('The order has not been reversed.'));
                Mage::logException($e);
            }
            $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
        }
    }

}

