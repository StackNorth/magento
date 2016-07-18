<?php
class D1m_WeiXinAPI_TestController extends Mage_Core_Controller_Front_Action{



    public function stockAction(){

       /* $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        $qty = $stock->getQty()-$qty ;
        if($qty>-1){

            $product->save();
        }*/

//        'stock_item' =>
//    object(Mage_CatalogInventory_Model_Stock_Item)[283]

        /** @var  $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('catalog/product');
        // Load product using product id
        $product->load(8321);

        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        var_dump($stock->getData());exit;
        var_dump($product->getStockItem());exit;

       $stockData['qty'] = 1;
//        if($v>0){
//            $stockData['is_in_stock'] = 1;
//        }else{
//            $stockData['is_in_stock'] = 0;
//        }

        $product->setStockData($stockData);
        $product->save();
    }




    public function indexAction(){
        /** @var  $collection Mage_Eav_Model_Entity_Attribute */
        /*$attributeInfo =  Mage::getResourceModel('eav/entity_attribute_collection')
                        ->setCodeFilter('province')
                        ->getFirstItem();
        //var_dump($attributeInfo);exit;
        //获得所有城市
        var_dump($attributeInfo->getSource()->getAllOptions(false));*/


        /** @var  $collection Mage_Eav_Model_Entity_Attribute */
        /*$attributeInfo =  Mage::getResourceModel('eav/entity_attribute_collection')
            ->setCodeFilter('n_shop')
            ->getFirstItem();
        //var_dump($attributeInfo);exit;
        //获得所有门店
        var_dump($attributeInfo->getSource()->getAllOptions(false));*/
    }

    public function productAction(){
        //$date = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));


        /** @var  $_productCollection Mage_Catalog_Model_Resource_Product_Collection */
        $_productCollection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToFilter('created_at',array('gt'=>'2015-12-25 00:00:00'))
            ->addAttributeToSort('created_at', 'DESC')
            ->addAttributeToSelect('*')
            ->load();

        var_dump($_productCollection->getSize());
    }

    public function cartRulesAction(){

       /**  @var $validator Mage_SalesRule_Model_Validator */
        $validator = Mage::getModel('salesrule/validator');
        $validator->process('');
       // var_dump($validator->process(''));
       // exit;

        /** @var  $collection Mage_SalesRule_Model_Resource_Rule_Collection */
       // $collection = Mage::getResourceModel('salesrule/rule_collection');
        //$collection->getItems();
       // var_dump($collection->getData());

        /** @var  $cart Mage_Checkout_Model_Cart */
        $cart=Mage::getSingleton('checkout/cart');
        /** @var  $quote Mage_Sales_Model_Quote */
        $quote=$cart->getQuote();
        $quote->getHobbyByItemId();
       // $appliedRuleIds=$quote->getAppliedRuleIds();
        //var_dump($appliedRuleIds);
    }


    public function couponAction(){
        /** @var  $coupon Mage_SalesRule_Model_Coupon */
        $coupon = Mage::getSingleton('salesrule/coupon');
        /** @var  $collection Mage_SalesRule_Model_Resource_Coupon_Collection */
        $collection = $coupon->getCollection();
        $collection = $collection
            ->join(array('sale_rule'=>'salesrule/rule'),
                        'sale_rule.rule_id=main_table.rule_id and sale_rule',
                        array(
                            'rule_id'=>'sale_rule.rule_id',
                            'conditions_serialized'=>'sale_rule.conditions_serialized',
                            'code'=>'main_table.code',
                            ))
            ->addFieldToFilter('main_table.code','SHB201501WS0E5P');
$tt = (string)$collection->getSelect();
//        var_dump($tt);exit;
        $item = $collection->getFirstItem();
        $cs = $item->getConditionsSerialized();

        $conditions = unserialize($cs);
        var_dump($conditions);

    }


    public function tt3Action(){


        /** @var $order D1m_WeiXinAPI_Model_Order_Api */
        $order = Mage::getModel('wxapi/order_api');

     /*   $data['orderId'] = '917';
        $data['credit_num'] = 0;
        $data['amount']= 0;
        $data['coupon_code']='HJWSET95HH5Y';
        $data['ruleText']='2015/1/6优惠券所有地区';
        $result = $order->updateOrder($data);

       var_dump($result);*/
        /** @var $course D1m_WeiXinAPI_Model_Course_Api */
        $course = Mage::getModel('wxapi/course_api');
        $data['credit_num'] = 200;
        $data['member_id']= '2016030113761774120';
        $data['created_at'] = Mage::getModel('core/date')->date('Y-m-d H:i:s');

        $result = $course->updateCredit($data);

        var_dump($result);




        /** @var  $customerModel Mage_Customer_Model_Customer */
        //$customerModel =  Mage::getModel('customer/customer');
        /** @var  $collection Mage_Customer_Model_Resource_Customer_Collection */
//        $collection = $customerModel->getCollection();
//
//        $collection->addFieldToFilter('increment_id',array('like'=>'%2016040113761774120%'));
//        $item = $collection->getFirstItem();
//        var_dump($item);
    }

    public function coAction(){
        $product = Mage::getModel('catalog/product')->setStoreId(2)->load('8221');
        /** @var  $customerModel Mage_Customer_Model_Customer */
        $customerModel =  Mage::getModel('customer/customer');
        $customer = $customerModel->setStore(Mage::app()->getStore(2))
            ->loadByEmail('269353248@qq.com');
        /** @var $helper D1m_WeiXinAPI_Helper_Data */
        $helper = Mage::helper('wxapi/data');
        $data['billing']=array('email'=>'269353248@qq.com');
        $address = $helper->validateAddress($data['billing']);
        /** @var  $quote Mage_Sales_Model_Quote */
        $quote=Mage::getModel('sales/quote')->setStoreId(2);

        $shippingAddress = $quote->getShippingAddress();
        $billingAddress  = $quote->getBillingAddress();
        $shippingAddress->addData($address);
        $billingAddress->addData($address);
        $shippingAddress->setEmail($customer->getEmail());
        $quote->assignCustomer($customer);
        $qty = 2;
        $quote->addProduct($product,2);

        $quote->getPayment()->importData(array('method' => 'alipay_payment'));
        $quote->setCouponCode('SHC1412TPDE2A0');
        $quote->collectTotals();
        $quote->save();
        var_dump($quote);
        exit;

        $items = $quote->getAllItems();

/*        var_dump($items);
exit;*/
        /**  @var $validator Mage_SalesRule_Model_Validator */
        $validator = Mage::getSingleton('salesrule/validator');
        $validator->init(1,$customer->getGroupId(),'SHC1412TPDE2A0');
//        $validator->
       /* $add = $quote->getAllAddresses();
        $validator->initTotals($items,$add[0]);*/

        $fd = $validator->process($items[0]);
//        $quote->add
       // var_dump($quote->getAppliedRuleIds());
       // var_dump($quote);

        $service = Mage::getModel('sales/service_quote', $quote);

        $service->submitAll();

        $quote->save();

    }

}