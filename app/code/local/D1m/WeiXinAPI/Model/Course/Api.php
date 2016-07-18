<?php

class D1m_WeiXinAPI_Model_Course_Api extends Mage_Api_Model_Resource_Abstract{
    /**
     * 获得城市
     *
     * @return mix
     */
    public function province(){
        try{
            /** @var  $collection Mage_Eav_Model_Entity_Attribute */
            $attributeInfo =  Mage::getResourceModel('eav/entity_attribute_collection')
                ->setCodeFilter('province')
                ->getFirstItem();
            //获得所有城市
            $provinces = $attributeInfo->getSource()->getAllOptions(false);
            return array('code'=>1,'data'=>$provinces);
        }catch (Mage_Core_Exception $e){
            return array('code'=>0,'message'=>$e->getMessage());
        }
    }

    /**
     * 获得门店
     * @return mix
     */
    public function store(){
        try{
            /** @var  $collection Mage_Eav_Model_Entity_Attribute */
            $attributeInfo =  Mage::getResourceModel('eav/entity_attribute_collection')
                ->setCodeFilter('n_shop')
                ->getFirstItem();
            //获得所有门店
            $stores = $attributeInfo->getSource()->getAllOptions(false);
            return array('code'=>1,'data'=>$stores);
        }catch (Mage_Core_Exception $e){
            return array('code'=>0,'message'=>$e->getMessage());
        }

    }

    /**
     * 获得课程
     * @param $data
     * @return mix
     */
    public function courses($data){
        //$date = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));

        if(empty($data)){
            $data['province']=12;//默认城市 上海
            $data['n_shop']=26;//默认门店  腾飞大厦
            $data['py']=date('Y');//默认年份
            $data['pm']=date('m');//默认月份
        }
        if(!$data['province']){
            return array('code'=>0,'message'=>'省份不能为空');
        }
        if(!$data['n_shop']){
            return array('code'=>0,'message'=>'门店不能为空');
        }
        if(!$data['py']){
            return array('code'=>0,'message'=>'年份不能为空');
        }
        if(!$data['pm']){
            return array('code'=>0,'message'=>'月份不能为空');
        }
        try{
            /** @var  $_productCollection Mage_Catalog_Model_Resource_Product_Collection */
            $_productCollection = Mage::getModel('catalog/product')
                ->getCollection()->setStore(2)
                ->addAttributeToFilter('province',array('eq'=>$data['province']))
                ->addAttributeToFilter('n_shop',array('eq'=>$data['n_shop']))
                ->addAttributeToFilter('class_date',array('gteq'=>$data['py'].'-02-01'))
                ->addAttributeToSort('class_date', 'DESC')
                ->addAttributeToSelect('*')
                //->addAttributeToSelect(array('name','sku'))
                ->load();

            /*$tt = (String)$_productCollection->getSelect();
            Mage::log($tt,null,'test11.log');*/
            $items = $_productCollection->getItems();
            $data = array();
            $i=0;
            foreach($items as $item){
                $data[$i]['entity_id']= $item->getId();//课程id
                $data[$i]['name']= $item->getName();//课程名称
                $data[$i]['province']= $item->getProvince();//城市
                $data[$i]['n_shop']= $item->getNShop();//门店地址
                $data[$i]['class_address']= $item->getClassAddress();//具体地址
                $data[$i]['class_date']= $item->getClassDate();//上课日期
                $data[$i]['n_classtime1']= $item->getNClasstime1();//上课开始时间
                $data[$i]['n_classtime2']= $item->getNClasstime2();//上课结束时间
                $data[$i]['price']= $item->getPrice();//课程价格
                $i++;
            }
            return array('code'=>1,'data'=>$data);

        }catch (Mage_Core_Exception $e){
            return array('code'=>0,'message'=>$e->getMessage());
        }
    }

    /**
     * 获得课点
     * @param $data
     * @return mixed
     */
    public function credit($data){

        if($data['member_id']){
            /** @var  $customerModel Mage_Customer_Model_Customer */
            $customerModel =  Mage::getModel('customer/customer');
            /** @var  $collection Mage_Customer_Model_Resource_Customer_Collection */
            $collection = $customerModel->getCollection();
            $collection->addFieldToFilter('increment_id',array('like'=>'%'.$data['member_id'].'%'));
            if($collection->getSize()>0){
                $customer = $collection->getFirstItem();

                try{

                    $collection = Mage::getModel('d1m_credits/credits')->getCollection();
                    $collection->addFieldToSelect('credit_amount');
                    $collection->addFieldToFilter('customer_id',array('eq'=>$customer->getId()))->getSelect();
                    if($collection->getSize()>0){
                        return array('code'=>1,'data'=>$collection->getFirstItem()->getCreditAmount());
                    }else{
                        return array('code'=>0,'message'=>'用户没有找到');
                    }
                }catch (Mage_Core_Exception $e){
                    return array('code'=>0,'message'=>$e->getMessage());
                }

            }else{
                return array('code'=>0,'message'=>'用户没有找到');
            }

        }else{
            return array('code'=>0,'message'=>'用户member id为必填项');
        }

    }


    /**
     * 获得产品库存
     * @param $data
     * @return array
     */
    public function getProductStock($data){
        if($data['sku']){
            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $data['sku']);
            $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
            return array('code'=>1,'data'=>$stock->getQty());
        }else{
            return array('code'=>0,'message'=>'产品sku不能为空');
        }
    }


    /**
     * 同步课点信息（购买记录与课点数更新）
     * @param $data
     * @return array
     */
    public function updateCredit($data){
        if(!$data['credit_num'] || !is_numeric($data['credit_num'])){
            return array('code'=>0,'message'=>'课点数不能为空并且为数字类型');
        }
        if(!$data['member_id']){
            return array('code'=>0,'message'=>'Member Id不能为空');
        }
        if(!$data['created_at']){
            return array('code'=>0,'message'=>'创建时间不能为空');
        }

        /** @var  $customerModel Mage_Customer_Model_Customer */
        $customerModel =  Mage::getModel('customer/customer');
        /** @var  $collection Mage_Customer_Model_Resource_Customer_Collection */
        $collection = $customerModel->getCollection();
        $collection->addFieldToFilter('increment_id',array('like'=>'%'.$data['member_id'].'%'));
        if($collection->getSize()>0){
            $customer = $collection->getFirstItem();
            $coData['customer_id']= $customer->getId();
            $coData['status']='complete';
            $coData['qty']=$data['credit_num'];
            $unit_price = Mage::getStoreConfig('d1m_credits/general/creditunit');
            $coData['unit_price'] = $unit_price;
            $coData['grand_total']=$unit_price*$data['credit_num'];
            $coData['payment_method']='wechat';
            $coData['created_at'] = $data['created_at'];
            /** @var  $co D1m_Credits_Model_Order */
            $co = Mage::getModel('d1m_credits/order');
            $co->setData($coData);
            try{
                $co->save();
                $credit = Mage::getModel('d1m_credits/credits');
                $collection = $credit->getCollection();
                $collection->addFieldToFilter('customer_id',$customer->getId());
                if($collection->getSize()>0){
                    $item = $collection->getFirstItem();

                    $credit->load($item->getId());
                    $creditNum = $item->getCreditAmount()+$data['credit_num'];
                    $credit->setData('credit_amount',$creditNum);
                    $credit->save();
                }else{
                    $creditData['credit_amount'] = $data['credit_num'];
                    $creditData['customer_id'] =$customer->getId();
                    $credit->setData($creditData);
                    $credit->save();
                }
                return array('code'=>1,'data'=>$credit->getCreditAmount());
            }catch (Mage_Core_Exception $e){
                return array('code'=>0,'message'=>$e->getMessage());
            }
        }else{
            return array('code'=>0,'message'=>'用户没有找到');
        }
        $item = $collection->getFirstItem();
    }

}
