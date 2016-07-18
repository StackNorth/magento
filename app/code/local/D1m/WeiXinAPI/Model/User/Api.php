<?php
class D1m_WeiXinAPI_Model_User_Api extends Mage_Api_Model_Resource_Abstract{


    /**
     * 用户登录
     * @param $data
     * @return mixed
     */
    public function login($data){
        if(!$data['phone']){
            return array('code'=>0,'message'=>'手机号为必填项');
        }
        if(!$data['password']){
            return array('code'=>0,'message'=>'密码为必填项');
        }
        /** @var  $customer D1m_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer');
        try{
            $collection = $customer->getCollection()->addFieldToFilter('phone', $data['phone']);
            if($collection->getsize()>0){
                $email = $collection->getFirstItem()->getEmail();
            }else{
                return array('code'=>0,'message'=>'手机号不存在');
            }

            $flag = $customer->setWebsiteId(1)->authenticate($email,$data['password']);
            if($flag){
                //$user = $customer->loadByEmail($email);
                return array('code'=>1,'message'=>'登录成功');
            }

            return array('code'=>0,'message'=>'登录失败');
        }catch (Mage_Core_Exception $e){
            return array('code'=>0,'message'=>$e->getMessage());
        }
    }

    /**
     * @param $data
     * @return array|false|Mage_Core_Model_Abstract
     */
    public function register($data){

        if(empty($data)){
            return array('code'=>0,'message'=>'手机号，用户名，邮箱，密码为必填项');
        }else{
            if(!$data['phone']){
                return array('code'=>0,'message'=>'手机号为必填项');
            }else if(!preg_match('/^[0-9]{11}$/',$data['phone'])){
                return array('code'=>0,'message'=>'手机号格式不正确');
            }else{
                $customer = $customer = $this->_getCustomer();
                $collection = $customer->getCollection()->addFieldToFilter('phone', $data['phone']);
                if($collection->getSize()>0){
                    return array('code'=>0,'message'=>'该手机号已被注册过');
                }
            }
            if(!$data['username']){
                return array('code'=>0,'message'=>'用户名为必填项');
            }else{
                $collection = $customer->getCollection()->addFieldToFilter('username', $data['username']);
                if($collection->getSize()>0){
                    return array('code'=>0,'message'=>'该用户名已经被使用');
                }
            }

            if(!$data['email']){
                return array('code'=>0,'message'=>'邮箱为必填项');
            }elseif(!preg_match('/^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/',$data['email'])){
                return array('code'=>0,'message'=>'邮箱格式不正确');
            }else{
                $collection = $customer->getCollection()->addFieldToFilter('email', $data['email']);
                if($collection->getSize()>0){
                    return array('code'=>0,'message'=>'该邮箱已被其他账号使用');
                }
            }
            if(!$data['password']){
                return array('code'=>0,'message'=>'密码不能为空');
            }elseif(strlen(trim($data['password']))<6){
                return array('code'=>0,'message'=>'密码最小长度为6位');
            }

            try{
                $data['website_id'] = 1;
                $data['store_id'] = 2;

                $customer->setData($data);
                $customerApi = Mage::getModel('customapi/memberservice');
                $customerCrm = array();
                $customerCrm['mobile'] = $customer->getPhone();
                $customerCrm['name'] = $customer->getUsername();
                $customerCrm['email'] = $customer->getEmail();
                $customerCrm['createDate'] = Mage::getModel('core/date')->date('Y-m-d H:i:s');
                $customerCrm['modifyDate'] = Mage::getModel('core/date')->date('Y-m-d H:i:s');
                $IncrementId=$customerApi->addMemberInfo($customerCrm,0);
                if($IncrementId) {
                    $customer->setIncrementId($IncrementId);
                }
                $customer->save();

                return array('code'=>1,'message'=>'注册成功');
            }catch (Mage_Core_Exception $e){
                return array('code'=>0,'message'=>$e->getMessage());
            }

        }

    }

    protected function _getCustomer(){

        $customer = Mage::getModel('customer/customer');
        return $customer ;

    }
}