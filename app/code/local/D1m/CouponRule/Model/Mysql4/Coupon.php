<?php
class D1m_CouponRule_Model_Mysql4_Coupon  extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('couponRule/coupon', 'coupon_id');
    }

    /**
     * 根据规则以及code获取coupon id
     * @param type $rule_id
     * @param type $coupon_code
     */
    public function loadByCouponcode($rule_id,$coupon_code){
         $read_resource  = Mage::getSingleton('core/resource')->getConnection('core_read');
         $tableName      = Mage::getSingleton('core/resource')->getTableName('couponRule/coupon');

         $select = $read_resource->select()->from($tableName)
            ->where('rule_id = :rule_id')
            ->where('coupon = :coupon');
         
        $data = $read_resource->fetchRow($select, array(':rule_id' => $rule_id, ':coupon' => $coupon_code));

        return $data['coupon_id'];
    }


    /** 根据用户名来获取相应的用户
     * @param array $customerGroup
     */
    public function getAllActiveAndNoCouponCustomer(array $customerGroup,$rule_id=NULL){
        $customer_collection = Mage::getModel('customer/customer')->getCollection()->addAttributeToSelect('entity_id')->addAttributeToSelect('email')->addFieldToFilter('group_id',array('in'=>$customerGroup));

        $customer_collection->getSelect()->joinLeft(array('lk' => new Zend_Db_Expr( '(' . Mage::getResourceModel('couponRule/coupon_collection')->getSelect() . ' WHERE main_table.rule_id='.$rule_id.')')), 'e.entity_id = lk.customer_id',  array('lk.customer_email'));

        $customer_collection->getSelect()->where('e.is_active=?',1)->where('lk.coupon IS NULL')->where('lk.customer_email IS NULL');

        return $customer_collection;
    }
    
    /**
     *  返回一个collection 
     * @param type $customer_id
     * @param type $avabile_rule_ids
     */
    public function getAllCouponListForCustomer($customer_id,$avabile_rule_ids,$sort_by='DESC'){
        if ($customer_id)
        {
            //get all customer coupon ,  not filter customer group
            $collection = Mage::getModel('salesrule/coupon')->getCollection()
              // ->addRuleToFilter($priceRule)
                   ->addFieldToFilter('lk.customer_id', $customer_id)
                   ->addFieldToFilter('main_table.is_primary', array('null' => 1));
                   // ->addFieldToFilter(array(array('attribute'=>'lk.customer_id','eq'=>(int)Mage::getSingleton('customer/session')->getId()),array('attribute'=>'main_table.is_primary',array('null' => 1))));
            $collection->getSelect()->joinLeft(
                         array('lk'=>Mage::getSingleton('core/resource')->getTableName('couponRule/coupon')),
                        'lk.coupon = main_table.code',
                         array('lk.customer_email'))
            ->join(
                       array('ss'=>Mage::getSingleton('core/resource')->getTableName('salesrule/rule')),
                       'ss.rule_id = main_table.rule_id',
                       array(
                           'rule_name'=>'ss.name',
                           'rule_description'=>'ss.description',
                           'from_date'=>'ss.from_date',
                           'to_date'=>'ss.to_date',
                           'is_credits'=>'ss.is_credits'
                           )
                   );

            //join all speical coupon
        if (!empty($avabile_rule_ids)) {
            $collection->getSelect()->orWhere('ss.coupon_type=? AND ss.use_auto_generation=0 AND  ss.rule_id IN ('.$avabile_rule_ids.')',Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC);
        }

            $collection->getSelect()->group('main_table.coupon_id')->order('ss.rule_id '.$sort_by);

            return $collection;
        }
        else
        {
            return new Varien_Data_Collection();
        }
    }


    /**
     *  根据用户组来获得已经激活的用户
     * @param array $customerGroup
     * @return mixed
     */
    public function getCustomerCollections(array $customerGroup){

        $customer_collection        = Mage::getResourceModel('customer/customer_collection')->addAttributeToSelect('*')->addAttributeToFilter('group_id', array('in' =>$customerGroup))->addAttributeToFilter('is_active', 1)
                                     ->joinAttribute('dob', 'customer/dob', 'entity_id', null, 'left')
                                     ->joinAttribute('gender', 'customer/gender', 'entity_id', null, 'left')
                                     ->joinAttribute('username', 'customer/username', 'entity_id', null, 'left')
                                     ->joinAttribute('mobile', 'customer/mobile', 'entity_id', null, 'left');

        return $customer_collection;
    }

    /**
     *   获取所有可用的促销规则
     */
    public function getAllAvaibleRuleCollections(){
      $rule_collections = Mage::getResourceModel('salesrule/rule_collection')->addFieldToFilter('is_active',1)
          ->addFieldToFilter('coupon_type',Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC)
          ->addFieldToFilter('to_date',array("notnull"=>true))->addFieldToFilter('to_date', array('gteq' => date('Y-m-d',time())))
          ->addFieldToFilter('primary_coupon.expire_notice_sent_flag',0);

      return $rule_collections;
    }

    /**
     * 根据规则获取相关的coupon
     */
    public function getCouponByRuleId(Mage_SalesRule_Model_Rule $rule){
        $collection = Mage::getResourceModel('salesrule/coupon_collection');

        $collection->addFieldToFilter('is_primary', array(array('eq' => 0), array('null' => 1)));

        $collection->getSelect()->join(
            array('lk'=>Mage::getSingleton('core/resource')->getTableName('couponRule/coupon')),
            'lk.coupon = main_table.code',
            array('lk.customer_email'));
        $collection->getSelect()->where('main_table.rule_id=?',$rule->getRuleId());
//Mage::helper('logger/data')->info((string)$collection->getSelect());

        return $collection;
    }


    /**
     *  促销规则ID
     * @param $rule_id
     */
    public function getCouponInfoByRuleId($rule_id){

        $customer_dob_attribute        = Mage::getModel('eav/entity_attribute')->loadByCode('customer', 'dob')->getAttributeId();
        $customer_username_attribute   = Mage::getModel('eav/entity_attribute')->loadByCode('customer', 'username')->getAttributeId();
        $customer_mobile_attribute     = Mage::getModel('eav/entity_attribute')->loadByCode('customer', 'mobile')->getAttributeId();

        $collection = Mage::getModel('salesrule/coupon')->getCollection()
            ->addFieldToFilter('main_table.rule_id', $rule_id)
            ->addFieldToFilter('main_table.is_primary', array('null' => 1));

//join salerule
        $collection->getSelect()->join(
            array('sr'=>Mage::getSingleton('core/resource')->getTableName('salesrule/rule')),
            'sr.rule_id = main_table.rule_id',
            array('name'=>'sr.name','description'=>'sr.description','rule_expire_date'=>'sr.to_date','rule_from_date'=>'sr.from_date')
        )->join(
                array('lk'=>Mage::getSingleton('core/resource')->getTableName('couponRule/coupon')),
                'lk.coupon = main_table.code',
                array('email'=>'lk.customer_email','coupon_code'=>'main_table.code','customer_id'=>'lk.customer_id')
            );
//inner customer EAV
        $collection->getSelect()->joinLeft(array("table_customer_datetime" => "customer_entity_datetime"), "table_customer_datetime.entity_id=lk.customer_id  and table_customer_datetime.attribute_id='$customer_dob_attribute'",array("dob" => "table_customer_datetime.value"))
            ->joinLeft(array("table_customer_var_mobile"    => "customer_entity_varchar"), "table_customer_var_mobile.entity_id=lk.customer_id  and table_customer_var_mobile.attribute_id='$customer_mobile_attribute'",array("mobile" => "table_customer_var_mobile.value"))
            ->joinLeft(array("table_customer_var_username"  => "customer_entity_varchar"), "table_customer_var_username.entity_id=lk.customer_id  and table_customer_var_username.attribute_id='$customer_username_attribute'",array("username" => "table_customer_var_username.value"));

        $collection->getSelect()->group('main_table.code');
//Mage::helper('logger/data')->info((string)$collection->getSelect());

        return $collection;
    }



    public function getNeedSendSmsToCustomer(){
        $coupon_type = join(',',array(D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_FOR_EVENT,D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_WITH_CUSTOMER));

        $customer_dob_attribute         = Mage::getModel('eav/entity_attribute')->loadByCode('customer', 'dob')->getAttributeId();
        $customer_firstname_attribute   = Mage::getModel('eav/entity_attribute')->loadByCode('customer', 'firstname')->getAttributeId();
        $customer_nickname_attribute    = Mage::getModel('eav/entity_attribute')->loadByCode('customer', 'nickname')->getAttributeId();
        $customer_lastname_attribute    = Mage::getModel('eav/entity_attribute')->loadByCode('customer', 'lastname')->getAttributeId();
        $customer_mobile_attribute      = Mage::getModel('eav/entity_attribute')->loadByCode('customer', 'mobile')->getAttributeId();

        $collection = Mage::getModel('salesrule/coupon')->getCollection()
            ->addFieldToFilter('main_table.expire_notice_sent_flag',0)
            ->addFieldToFilter('main_table.is_primary', array('null' => 1));


//join salerule
        $collection->getSelect()->join(
            array('sr'=>Mage::getSingleton('core/resource')->getTableName('salesrule/rule')),
            'sr.rule_id = main_table.rule_id',
            array('rule_name'=>'sr.name','rule_descripion'=>'sr.description','rule_expire_date'=>'sr.to_date','rule_from_date'=>'sr.from_date')
        )->join(
                array('lk'=>Mage::getSingleton('core/resource')->getTableName('couponRule/coupon')),
                'lk.coupon = main_table.code',
                array('lk.customer_email','lk.customer_id')
            );

//inner customer EAV
        $collection->getSelect()->joinLeft(array("table_customer_datetime" => "customer_entity_datetime"), "table_customer_datetime.entity_id=lk.customer_id  and table_customer_datetime.attribute_id='$customer_dob_attribute'",array("dob" => "table_customer_datetime.value"))
            ->joinLeft(array("table_customer_var_firstname" => "customer_entity_varchar"), "table_customer_var_firstname.entity_id=lk.customer_id  and table_customer_var_firstname.attribute_id='$customer_firstname_attribute'",array("firstname" => "table_customer_var_firstname.value"))
            ->joinLeft(array("table_customer_var_lastname"  => "customer_entity_varchar"), "table_customer_var_lastname.entity_id=lk.customer_id  and table_customer_var_lastname.attribute_id='$customer_lastname_attribute'",array("lastname" => "table_customer_var_lastname.value"))
            ->joinLeft(array("table_customer_var_mobile"    => "customer_entity_varchar"), "table_customer_var_mobile.entity_id=lk.customer_id  and table_customer_var_mobile.attribute_id='$customer_mobile_attribute'",array("mobile" => "table_customer_var_mobile.value"))
            ->joinLeft(array("table_customer_var_nickname"  => "customer_entity_varchar"), "table_customer_var_nickname.entity_id=lk.customer_id  and table_customer_var_nickname.attribute_id='$customer_nickname_attribute'",array("nickname" => "table_customer_var_nickname.value"));


        //add customer firstname
        $collection->getSelect()->where('sr.is_active=1 AND sr.coupon_type in ('.$coupon_type.')')->where('(sr.to_date IS NOT NULL) OR (main_table.expiration_date IS NOT NULl)');
        $collection->getSelect()->where('IFNULL(main_table.expiration_date,sr.to_date)>=?',date('Y-m-d'));

        $collection->getSelect()->group('main_table.code');
        return $collection;
    }

    /**
     *  批量更新coupon为已经发送
     * @param array $coupon_ids
     */
    public function batchUpdateMessageStatus(array $coupon_ids){
        $coupon_ids_str = join(',',$coupon_ids);
        $write_resource = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableName      = Mage::getSingleton('core/resource')->getTableName('salesrule/coupon');

        try {
                $query = "UPDATE {$tableName} SET expire_notice_sent_flag = 1 WHERE expire_notice_sent_flag = 0  AND coupon_id in ({$coupon_ids_str})";
                if ($write_resource->query($query)){
                    return true;
                } else {
                    Mage::log('send sms message error for notice expire notice! '.null,'sms_send.log');
                    Mage::log('this message is sent for expire notice that  coupon tpe is AutoMatic or Generate coupon  '.null,D1m_Core_Helper_Log::SEND_SMS_MESSAGE_LOG_FILE);
                    Mage::log('the update sql: '.$query.null,D1m_Core_Helper_Log::SEND_SMS_MESSAGE_LOG_FILE);
                }
            } catch (Mage_Core_Exception $e) {
                    Mage::log('send expire notice message had exception . Please view the excepion log. table name  '.$tableName,null,D1m_Core_Helper_Log::SEND_SMS_MESSAGE_LOG_FILE);
                    Mage::logException($e);
            }
    }


    public function loadPrimaryByCouponCode(Mage_SalesRule_Model_Coupon $object, $couponCode)
    {
        $read = $this->_getReadAdapter();
        $tableName =  Mage::getSingleton('core/resource')->getTableName('salesrule/coupon');
        //$write = $this->_getWriteAdapter();
        $select = $read->select()->from($tableName)
            ->where('code=?', $couponCode)
            ->where('is_primary=?', 1);

        $data = $read->fetchRow($select);

        if (!$data) {
            return false;
        }

        $object->setData($data);

        $this->_afterLoad($object);
        return $object;
    }
}
