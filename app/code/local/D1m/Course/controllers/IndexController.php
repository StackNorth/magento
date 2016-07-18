<?php
class D1m_Course_IndexController extends Mage_Core_Controller_Front_Action
{
   
   /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }
    

    
    function viewAction()
    {
        
        //if ($root = $this->getLayout()->getBlock('root'))
        //{
            //$root->addBodyClass('categorypath-' . $category->getUrlPath())                ->addBodyClass('category-' . $category->getUrlKey());
        //}

        $this->_initLayoutMessages('catalog/session');
        
        $this->loadLayout();
        $this->renderLayout();
    }
    
    
    function optionsAction()
    {
    	$filters = array();
    	$filters['monthdate'] = Mage::helper('d1m_course')->getALLMonthsSelects();
    	$filters['coursetype'] = Mage::helper('d1m_course')->getAllCourseTypeSelects();
    	$filters['province'] = Mage::helper('d1m_course')->getAllProvinceSelects();
    	$filters['fixeddate'] = '';
    	
    	$result = array();
    	$result['status'] = 1;
    	$result['filters'] = $filters;
    	
    	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    

    function calendarAction()
    {
    	$this->loadLayout(false);
        $this->renderLayout();
    }
    
    function scheduleajaxAction()
    {
    	$this->loadLayout(false);
        $this->renderLayout();
    }
    function scheduleajax_mobileAction()
    {
        //$this->loadLayout(false);
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('查看课程'));
        $this->renderLayout();
    }
    
    function scheduleAction()
    {
        
        if ($root = $this->getLayout()->getBlock('root'))
        {
             //$root->addBodyClass('categorypath-' . $category->getUrlPath())                 ->addBodyClass('category-' . $category->getUrlKey());
        }

        $this->_initLayoutMessages('catalog/session');
        
        $this->loadLayout();
        $this->renderLayout();
    }
    
	public function viewByAjaxAction()
    {
        
            $showway = $this->getRequest()->getParam('showway','json');
          
            $curPage  = $this->getRequest()->getParam('p','1');
            $dir 	  = $this->getRequest()->getParam('dir','desc');
            
			$result = $this->_initResultApiJsonArray(24 ,$curPage, $dir);
			
            if($showway == 'json')
            	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            else
            {
            	print_r($result); 
            	exit();
            }
           
            
        
        
    }
    
    
    private function _initResultApiJsonArray($pageSize = 24, $curPage = 1, $dir = 'desc', $sortby='is_special_price')
    {
    	
    		$update = $this->getLayout()->getUpdate();
            $update->addHandle('newonsale_new_byajax');
			$this->loadLayoutUpdates();
            $this->generateLayoutXml()->generateLayoutBlocks();
            
            Mage::getModel('catalog/layer_filter_appliedlayer')->apply($this->getRequest());
            
            //$allBlocks 	 = $this->getLayout()->getAllBlocks();
            $productlist = $this->getLayout()->getBlock('new_result_list');
            
            $_productCollection = $productlist->getLoadedProductCollection();
            
            //$sql = $_productCollection->getSelect()->__tostring();
            
            //Mage::log($sql);
            //Mage::log('cache key: '.$_productCollection->getCacheKey());
            //Mage::log('size: '.$_productCollection->getSize());
            
            $result = array();
            $result['id'] = 1;
            
            if($curPage == 1)
            {
		        $result['name'] = '新品';
		        $result['thumbnail'] = '';
		        $result['image'] 	 = '';
		        $result['description'] = '';
            }
            
            $result['p'] = $curPage;
            
            $toolbar = $productlist->getToolbarBlock();
            $productlist->initToolbarCollection();
            $productlist->setSortBy($sortby);
            //_productCollection->setPageSize($pageSize)->setCurPage($curPage);
            
            $result['sortby'] = $productlist->getSortBy();
            $productlist->setDefaultDirection($dir);
            $result['dir'] = $productlist->getDefaultDirection();
            
            //echo $_productCollection->getSelect().'<br/>';
            //echo $_productCollection->getSize();
            //exit();
            
            Mage::getModel('cataloginventory/stock')->addItemsToProducts($_productCollection);
            Mage::getModel('uemall_service/listindexer')->addItemsToProducts($_productCollection);
            
        	//Mage::getModel('review/review')->appendSummary($_productCollection);
            
            $result['firstnum'] = $toolbar->getFirstNum();
            $result['lastnum'] = $toolbar->getLastNum();
            $result['totals'] = $toolbar->getTotalNum();
			$result['limit'] = $toolbar->getLimit();
            $result['listtotal'] = $toolbar->getLastNum() - $toolbar->getFirstNum() + 1;
            $result['imgpath'] 	 = Mage::helper('catalog/product_ajax')->getBaseMediaUrl();
            //echo 'totols:'.$result['totals'].'<br/>';
            //exit();
            
            $result['items']  = array();
            foreach ($_productCollection as $_item)
            {
				$data = Mage::helper('catalog/product_ajax')->getProductJsonArray($_item);
				$result['items'][] = $data;
            }
    	
    		return $result;
    }



    //获取日历
    public function newcalendarAction()
    {
        /* @var  $block D1m_Course_Block_Calendar */

        $x= $this->getLayout()->createBlock('d1m_course/calendar') ->setBlockId('abc')->setTemplate('course/newcalendar.phtml')->toHtml() ;
        $this->getResponse()->setBody($x);

        // die();
        //$block=$this->getLayout()->createBlock('d1m_course/calendar');
        //$block->setBlockId('abc');
        //$block->setTemplate('course/newcalendar.phtml');
        //$x=$block->toHtml();
        //if ($x=='abcd') echo 'abcd';
        //echo $x;
        // die();
        // http://ay.com/course/index/newcalendar?py=2014&pm=1&province=12&pcat=all
    }

    //获取下拉 年月城市课程类型
    public function chooseAction()
    {
        $x= $this->getLayout()->createBlock('d1m_course/choose') ->setBlockId('abc')->setTemplate('course/choose.phtml')->toHtml() ;
        $this->getResponse()->setBody($x);

    }
    //取消订单
    public function cancelorderAction()
    {

        //48小时内的，由客服处理
        //48小时外，非纯课点支付的，由客服处理
        //48小时外，完全由课点支付的，退相应课点数,如果期间课点单价变化了，由客服处理
        //48小时外，完全现金支付的，是280整数倍的，可以取消，退客户相应的课点

        //参数 orderid
        $id=$this->getRequest()->getParam('id','');
        if ($id=="")  die("invalid parameter");
        if (!preg_match('/^[0-9]{1,}$/',$id))   die("invalid parameter");
        if ($id<0)  die("invalid parameter");

        $customer_id = $this->_getSession()->getCustomerId();
        if ($customer_id<=0)
        {
            //需要登录
            $this->_redirect('customer/account/login');
            return ;
        }
        //要求订单是自己下的并且支付完成状态
        $resource = Mage::getSingleton('core/resource');
        $order_table = $resource->getTableName('sales/order');
        $orderitem_table = $resource->getTableName('sales/order_item');
        $ordergrid_table = $resource->getTableName('sales/order_grid');
        $orderhistory_table = $resource->getTableName('sales/order_status_history');

        /* @var $dbr Magento_Db_Adapter_Pdo_Mysql */
        $dbr =$resource->getConnection ('core_read' );
        $sql="select count(*) from $orderitem_table where order_id='$id' ";
        $obj = $dbr->fetchRow($sql);
        $cc=$obj['count(*)'];
        if ($cc!=1)
        {
            $this->_getSession()->addError('订单有多个课程，不能自动取消，请与客户联系'); //一般情况下只有一个课程
            $this->_redirect('customer/account');
            return;
        }
        $sql="select product_id from $orderitem_table where order_id='$id' ";
        $obj = $dbr->fetchRow($sql);
        $oid=$obj['product_id'];
        //取产品的开课日期
        $item=Mage::getModel('catalog/product')->load($oid);
        if ($item->getId()!=$oid) die('unexpected error');
        $d1=substr($item->getClassDate(),0,10).' '.$item->getNClasstime1();
        $time  = mktime(date('H')+8, date('i'), 0, date("m") , date("d")+2, date("Y"));//UTC->GMT
        $d2=date("Y-m-d H:i",$time);
        if ($d1<$d2)
        {
            //48小时内的
            $this->_getSession()->addSuccess('48小时内开课的，要取消订单，请与网站客服联系。');
            $this->_redirect('customer/account');
            return ;
        }


        $sql="    SELECT *  FROM  $order_table    WHERE   entity_id='$id'    AND customer_id='$customer_id'     and status='complete'    ";
        $obj = $dbr->fetchRow($sql);
        if (!$obj)
        {
            $this->_getSession()->addError('必须是客户支付成功的订单才能在此取消');
            $this->_redirect('customer/account');
            return;
        }

        $increment_id=$obj['increment_id'];
        $credit_amount=$obj['credit_amount'];
        $rewardpoints_amount=$obj['rewardpoints_amount'];
        $grand_total=$obj['grand_total'];
        $credit_qty=$obj['credit_qty'];

        $creditunit = Mage::getStoreConfig('d1m_credits/general/creditunit');
        if ($creditunit<=0) $creditunit=280; //default 280

        $ok =0 ;

        //完全用课点支付的情况
        if ( ($grand_total==0 ) and ($rewardpoints_amount==0) and ($credit_amount<0) ) $ok=1;
        //完全用现金支付的情况
        if ( ($grand_total>0 ) and ($rewardpoints_amount == 0) and ($credit_amount == 0) ) $ok=2;

        if (0==$ok)
        {
            //其它 情况由客服处理, 输出html?
            $this->_getSession()->addSuccess('要取消订单，请与网站客服联系。');
            $this->_redirect('customer/account');
            return ;
        }

        //课点支付
        if ( 1==$ok)
        {
            if (abs($credit_amount)!=$credit_qty*$creditunit)
            {
                //课点价格改变了，不处理
                $this->_getSession()->addError('本订单不能自动处理，请联系网站客服取消订单');
                $this->_redirect('customer/account');
                return ;
            }

            $ret=$credit_amount / $creditunit;
            $ret=floor($ret);
            if ($ret * $creditunit!= $credit_amount) //有误差
            {
                $this->_getSession()->addError('本订单不能自动处理，请联系网站客服取消订单');
                $this->_redirect('customer/account');
                return ;
            }
            $ret=-$ret; //负数变正数

        }
        else
        {

            $ret=$grand_total / $creditunit; //返课点
            $ret=floor($ret);
            if ($ret * $creditunit!= $grand_total) //有误差时
            {
                $this->_getSession()->addError('本订单不能自动处理，请联系网站客服取消订单');
                $this->_redirect('customer/account');
                return ;
            }
        }


        /* @var $dbw Magento_Db_Adapter_Pdo_Mysql */
        $dbw=$resource->getConnection ('core_write' );
        $sql=" update $order_table set status='canceled',state='cancel' WHERE   entity_id='$id'    AND customer_id='$customer_id'     and status='complete' ";
        try
        {
          //  mage::log($sql);
            $dbw->query($sql);
            $order = Mage::getModel('sales/order')->load($id);
            $now = Mage::getModel('core/date')->date('Y-m-d H:i:s');
            $order->setUpdatedAt($now)->save();
            Robi_Settleaccount_Model_Observer::sales_order_cancel_after_customergroup($order);

            Mage::getModel('d1m_adminhtml/sales_order_grid_collection')->updateOrderStatus($id,'refund');


        }
        catch (exception $e)
        {
            $this->_getSession()->addError('数据库操作失败');
            $this->_redirect('customer/account');
            return ;
        }

     /*   $sql="update $ordergrid_table set status='refund' WHERE   entity_id='$id'    AND customer_id='$customer_id'     and status='complete' ";
        try
        {
            //mage::log($sql);
            $dbw->query($sql);
        }
        catch (exception $e)
        {
            $this->_getSession()->addError('数据库操作失败');
            Mage::log("fatal error, sql fail: $sql");
            $this->_redirect('customer/account');
            return ;
        }*/


        $now=Varien_Date::formatDate(time(), true);
        $sql="insert into $orderhistory_table (parent_id,is_customer_notified,is_visible_on_front,comment,status,entity_name,created_at)
         values    (   '$id','0','0','订单被用户取消,返还课点".$ret."','canceled','order','$now'         ) ";
        try
        {
            $dbw->query($sql);
        }
        catch (exception $e)
        {
            Mage::log("error: sql fail: $sql");
        }

        /* @var $obj D1m_Credits_Model_Credits */
        $obj = Mage::getModel('d1m_credits/credits')->load($customer_id, 'customer_id');
        if(!$obj || $obj->getId() <= 0)
        {
            $obj = Mage::getModel('d1m_credits/credits');
            $obj->customer_id = $customer_id;
            $obj->credit_amount = 0;
        }
        $obj->credit_amount = $obj->credit_amount + $ret;
        $obj->historyDesc='客户自己取消订单返课点';
        $obj->historyOrderNo=$increment_id;
        try
        {
            $obj->save();
            $this->_getSession()->addSuccess('订单取消成功');
        }
        catch (exception $e)
        {
            Mage::log('客户编号'.$customer_id.',订单编号'.$increment_id.',应返课点'.$ret.',操作失败，请人工处理');
            $this->_getSession()->addError('订单取消成功,但返课点失败，请与网站客服联络处理');
        }
        $this->_redirect('customer/account');


    }

    //下载菜谱
    public function downloadAction()
    {
        //传递参数订单号
        //产品名称 urldecode
        //课程日期 2个月内
        $id=$this->getRequest()->getParam('id',0);
        settype($id,"integer");
        if ($id<=0) return ;
        $pname=$this->getRequest()->getParam('pname','');
        $pname=urldecode($pname);
        if ($pname=="") return ;
        /* @var $resource Mage_Core_Model_Resource */
        $resource = Mage::getSingleton('core/resource');
        $tablea = $resource->getTableName('sales/order_item');
        $tableb = $resource->getTableName('sales/order');
        $tablec = $resource->getTableName('catalog/product');
        $tabled = $resource->getTableName('catalog_product_entity_varchar');
        $tablee = $resource->getTableName('catalog_product_entity_datetime');


        $productResource = Mage::getResourceSingleton('catalog/product');
        $attrname = $productResource->getAttribute('name');
        $attrnameId = $attrname->getAttributeId();
        $attrname = $productResource->getAttribute('class_date');
        $attrclassdateId = $attrname->getAttributeId();


        //取课程日期
        $sql="
SELECT e.value FROM
$tablea a,$tableb b,$tablec c,$tabled d,$tablee e
WHERE
a.order_id=b.entity_id   AND b.status='complete'
AND a.product_id=c.entity_id
AND c.entity_id=d.entity_id
AND d.entity_type_id=4
AND d.attribute_id= '$attrnameId'
AND d.store_id=0
AND d.value=?
AND c.entity_id=e.entity_id
AND e.entity_type_id=4
AND e.attribute_id='$attrclassdateId'
AND e.store_id=0
order by e.value desc
limit 0,1
";
        $dbr=$resource->getConnection ('core_read' );
        $pdate=$dbr->fetchOne($sql,array($pname)); //fetchCol array
        if ($pdate=="")
        {

            $this->_getSession()->addError('无效的订单id');
            $this->_redirect('customer/account');
            return ;

        }
        $pdate=substr($pdate,0,10);
        $d1=new DateTime($pdate);
        $d1->add(new DateInterval('P2M'));
       /* $s1= $d1->format('Y-m-d');
        if ( $s1< date("Y-m-d"))
        {
            //过了2个月
            $this->_getSession()->addError('菜谱只能在开课后2个月内下载');
            $this->_redirect('customer/account');
            return ;
        }*/
        //是否有下载的资料?
        /* @var $helper D1m_Producttool_Helper_Data */
        $helper=Mage::helper('d1m_producttool');
        $arr=$helper->getdownloadinfo($pname);
        if ($arr=="")
        {
            $this->_getSession()->addError('当前菜谱文件不存在');
            $this->_redirect('customer/account');
            return ;
        }
        $fname=$arr[0];
        $fn=$arr[1];
        if (!is_file($fn))
        {
            $this->_getSession()->addError('当前菜谱文件无效');
            $this->_redirect('customer/account');
            return ;
        }

        $content=array('type'=>'filename','value'=>$fn);
        $fname=urlencode($fname);
        $this->_prepareDownloadResponse($fname, $content);

    }
    public function reorderAction()
    {
        //没登录先登录
        if (! Mage::getSingleton('customer/session')->isLoggedIn() )
        {
            $params=$this->getRequest()->getParams();
            $url=mage::geturl('*/*/*',$params);
            //die($url);
            Mage::getSingleton('customer/session')->setBeforeAuthUrl($url);
            $this->_redirect('customer/account/login');
            return;
        }
        //id是订单号
        $id=$this->getRequest()->getParam('id',0);
        settype($id,"integer");
        if ($id<=0) return;

        //根据订单获取信息
        //要求单一产品
        /* @var $model Mage_Sales_Model_Order */
        $model=Mage::getModel('sales/order');
        $model->load($id);
        $id=$model->getId();
        if ($id<=0) return;
        if ($model->getData('status')!='complete') return ;

        //要求是本人的订单
        if (Mage::getSingleton('customer/session')->getCustomerId()!=$model->getCustomerId()) return ;
        //订购产品只有一种

        /* @var $collection Mage_Sales_Model_Resource_Order_Item_Collection */
        $collection = Mage::getResourceModel('sales/order_item_collection');
        $collection->addFieldToFilter('order_id',$id);
        if ( $collection->getSize()!=1)
        {
            $this->_getSession()->addError('只有购买单一课程的订单才可以换课');
            $this->_redirect('customer/account');
            return ;
        }
        $item=$collection->getFirstItem();
        $oid=$item->getData('product_id');
        if ($oid==null) return;

        //取产品属性 开课日期在后天的才能换
        /* @var $courses D1m_Course_Model_Mysql4_Course_Collection */
        $courses =  Mage::getResourceModel('d1m_course/course_collection')
            //->addAttributeToSelect('name')
            //->addAttributeToSelect('class_address')
            //->addAttributeToSelect('province')
            ->addAttributeToSelect('class_date')
            ->addAttributeToSelect('n_classtime1');
            //->addAttributeToSelect('n_classtime2');
        $courses->addFieldToFilter('entity_id', $oid);

        $item=$courses->getFirstItem();
        if ($item->getId()!=$oid) return ;

        $d1=substr($item->getClassDate(),0,10).' '.$item->getNClasstime1();
        $time  = mktime(date('H')+8, date('i'), 0, date("m") , date("d")+2, date("Y"));//UTC->GMT
        $d2=date("Y-m-d H:i",$time);
        if ( $d1<$d2)
        {
            $this->_getSession()->addError('只有48小时后开课的课程才能换课');
            $this->_redirect('customer/account');
            return ;
        }

        //显示换课提示
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('换课提示'));
        $this->renderLayout();

    }

    public function reorder2Action()
    {

        //没登录先登录
        if (! Mage::getSingleton('customer/session')->isLoggedIn() )
        {
            //以前的网址参数无用
            $this->_redirect('customer/account/login');
            return;
        }

        //显示换课确认
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('换课确认'));
        $this->renderLayout();

    }

    public function reorder3Action()
    {
        //没登录先登录
        if (! Mage::getSingleton('customer/session')->isLoggedIn() )
        {
            //以前的网址参数无用
            $this->_redirect('customer/account/login');
            return;
        }

        //换课模式
        $mode=Mage::getSingleton('customer/session')->getData('reorder_mode');
        if ($mode!='1')
        {
           // die('非换课模式');
            $this->_redirect('customer/account/login');
            return;
        }
        //订单编号
        $id=Mage::getSingleton('customer/session')->getData('reorder_orderid');
        settype($id,"integer");
        if ($id<=0)
        {
           // die('无订单号');
            $this->_redirect('customer/account/login');
            return;
        }
        //新产品编号
        $nid=$this->getRequest()->getParam('nid',0);
        settype($nid,"integer");
        if ($nid<=0)
        {
          //  die('无新产品编号');
            $this->_redirect('customer/account/login');
            return;
        }


        //检查订单id 取旧产品oid
        //根据订单获取信息
        //要求单一产品
        /* @var $order Mage_Sales_Model_Order */
        $order=Mage::getModel('sales/order');
        $order->load($id);
        $id=$order->getId();
        if ($id<=0) return;
        if ($order->getData('status')!='complete') return ;

        $quote_id=$order->getQuoteId();

        /* @var $invoiceCollection Mage_Sales_Model_Mysql4_Order_Invoice_Collection */
        $invoiceCollection=$order->getInvoiceCollection();
        $arrinvoice_id=array();
        if (!is_null($invoiceCollection))
            foreach ($invoiceCollection as $invoice)
            {
                $arrinvoice_id[]=$invoice->getId();
            }

        //总金额，不计折扣
        $subtotal=$order->getData('subtotal');
        $qty=$order->getData('total_qty_ordered');


        //要求是本人的订单
        if (Mage::getSingleton('customer/session')->getCustomerId()!=$order->getCustomerId()) return ;

        /* @var $collection Mage_Sales_Model_Resource_Order_Item_Collection */
        $collection = Mage::getResourceModel('sales/order_item_collection');
        $collection->addFieldToFilter('order_id',$id);
        if ( $collection->getSize()!=1)
        {
            $this->_getSession()->addError('只有购买单一课程的订单才可以换课');
            $this->_redirect('customer/account');
            return ;
        }

        $item=$collection->getFirstItem();
        $oid=$item->getData('product_id');
        if ($oid==null) return;

        //取旧产品属性 开课日期在后天的才能换
        /* @var $item Mage_Catalog_Model_Product */
        $item=Mage::getModel('catalog/product')->load($oid);
        if ($item->getId()!=$oid) return ;
        $oldname=$item->getdata('name').'('.substr($item->getdata('class_date'),0,10).' '.$item->getData('n_classtime1').'-'.$item->getData('n_classtime2').')';


        $d1=substr($item->getClassDate(),0,10).' '.$item->getNClasstime1();
        $time  = mktime(date('H')+8, date('i'), 0, date("m") , date("d")+2, date("Y"));//UTC->GMT
        $d2=date("Y-m-d H:i",$time);
        if ( $d1<$d2)
        {
            $this->_getSession()->addError('只有48小时后开课的课程才能换课');
            $this->_redirect('customer/account');
            return ;
        }
        //检查新产品nid
        if ($nid==$oid)
        {
            $this->_getSession()->addError('必须是不同名称或开课时间的课程才能更换');
            $this->_redirect('customer/account');
            return ;
        }

        //检查新产品价格
        $item=Mage::getModel('catalog/product')->load($nid);
        if ($item->getId()!=$nid) return ;
        $newname=$item->getdata('name').'('.substr($item->getdata('class_date'),0,10).' '.$item->getData('n_classtime1').'-'.$item->getData('n_classtime2').')';
        $newsku=$item->getdata('sku');
        $new_name=$item->getdata('name');

        $price=$item->getPrice();
        if ($subtotal!= $price* $qty)
        {
            $qty=(int)$qty;
            $this->_getSession()->addError('原订单总额为'.$subtotal.',更换后的课程价格为'.$price.',数量'.$qty.',价格*数量与总额不一致');
            $this->_redirect('customer/account');
            return ;
        }

        //非禁用
        if ($item->getStatus()!=Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
        {
            $this->_getSession()->addError('新课程已禁止购买，请重新选择');
            $this->_redirect('customer/account');
            return ;
        }
        //检查新产品库存
        $num=(int)$item->getStockItem()->getQty();
        if ($num<$qty)
        {
            $this->_getSession()->addError('新课程可预订座位为'.$num,',小于原购买数量'.$qty.'，请重新选择');
            $this->_redirect('customer/account');
            return ;
        }
        //执行换课操作,调整新旧产品库存 假定全是虚拟产品，成功订单，没有退款及运输信息
   

   
        $resource=Mage::getSingleton('core/resource');
        $t_sales_flat_quote_item = $resource->getTableName('sales_flat_quote_item');
        $t_sales_flat_quote_item_option = $resource->getTableName('sales_flat_quote_item_option');
        $arrsql=array();
        $arrsql[]="update $t_sales_flat_quote_item set product_id='$nid' where quote_id='$quote_id'  and product_id='$oid' ";

        /* @var $dbr Magento_Db_Adapter_Pdo_Mysql */
        $dbr=$resource->getConnection ('core_read' );
        $sql="select item_id from $t_sales_flat_quote_item  where quote_id='$quote_id' ";
        $obj = $dbr->fetchAll($sql);
        foreach($obj as $row)
        {
            $quote_item_id=$row['item_id'];
            $arrsql[]="update $t_sales_flat_quote_item_option set product_id='$nid' where product_id='$oid' and item_id='$quote_item_id' ";
        }


        //invoice 可能有多张
        $t_sales_flat_invoice_item =$resource->getTableName('sales_flat_invoice_item');
        for ($i=0;$i<count($arrinvoice_id);$i++)
        {
            $parent_id=$arrinvoice_id[$i];
            $arrsql[]="update $t_sales_flat_invoice_item set product_id='$nid' where parent_id='$parent_id' ";
        }
        $t_sales_flat_order_item=$resource->getTableName('sales_flat_order_item');




        $new_name=addslashes($new_name);
        $arrsql[]="update $t_sales_flat_order_item set product_id='$nid',sku='$newsku',name='$new_name' where order_id='$id' and product_id='$oid'  ";
        //die("update $t_sales_flat_order_item set product_id='$nid',sku='$osku',name='$oname', where order_id='$id' and product_id='$oid'  ");

        // product_options 暂时不改变，会不一致！
        //a:1:{s:15:"info_buyRequest";a:5:{s:4:"uenc";s:52:"aHR0cDovL2F5LmNvbS9jb3Vyc2UvaW5kZXgvc2NoZWR1bGVhamF4";s:7:"product";s:3:"440";s:8:"form_key";s:16:"BIPyXNkSxh2tv8xG";s:1:"&";s:0:"";s:3:"qty";s:1:"1";}}

        // aca_sales_flat_order_status_history;
        $message = '用户换课,原课程为'.$oldname.'，新课程为'.$newname;
        //mage::log($message);
        $order->addStatusHistoryComment($message);
        $order->save();

        /* 执行sql语句 */
        /* @var $dbw Magento_Db_Adapter_Pdo_Mysql */
        $dbw=$resource->getConnection ('core_write' );
        for ($i=0;$i<count($arrsql);$i++)
        {
            $sql=$arrsql[$i];
            //echo $sql; echo "<br/>";
            //mage::log($sql);
            try
                {
                    $dbw->query($sql);
                }
            catch (exception $e)
                {
                   mage::log('error sql:'.$sql);
                }
        }

        //更改库存
        /* @var $product Mage_Catalog_Model_Product */
        $product=Mage::getModel('catalog/product')->load($oid);
        $num=(int)$product->getStockItem()->getQty();
        $product->setStockData(array( 'is_in_stock' =>1, 'qty' => $num + $qty ));
        $product->save();

        $product=Mage::getModel('catalog/product')->load($nid);
        $num=(int)$product->getStockItem()->getQty();
        if ($num-$qty<=0)
           $product->setStockData(array( 'is_in_stock' =>0, 'qty' => 0));
        else
           $product->setStockData(array( 'is_in_stock' =>1, 'qty' => $num - $qty));
        $product->save();


        // die('ok');
        //成功后清除换课模式
        Mage::getSingleton('customer/session')->setData('reorder_mode',0);
        Mage::getSingleton('customer/session')->setData('reorder_orderid',0);
        $url=Mage::getUrl('customer/account');
        $this->_redirectUrl($url);

    }
    public function reorderinAction()
    {
        $id=$this->getRequest()->getParam('id',0);
        Mage::getSingleton('customer/session')->setData('reorder_mode',1);
        Mage::getSingleton('customer/session')->setData('reorder_orderid',$id);
        $url=Mage::getBaseUrl().'#3';
        $this->_redirectUrl($url);

    }
    public function reorderoutAction()
    {
        Mage::getSingleton('customer/session')->setData('reorder_mode',0);
        Mage::getSingleton('customer/session')->setData('reorder_orderid',0);
        $url=Mage::getBaseUrl().'#3';
        $this->_redirectUrl($url);

    }


}