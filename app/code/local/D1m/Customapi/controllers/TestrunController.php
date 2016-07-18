<?php
class D1m_Customapi_testrunController extends Mage_Core_Controller_Front_Action
{
    
    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
        // a brute-force protection here would be nice

        parent::preDispatch();

    }

    public function testAction(){
       // $configUrl  = 'http://192.168.100.22:8090/aCRM_Fissler/webService/memberService?wsdl';// Produce

        // $client= new SoapClient($configUrl);

      //  print_r($client);
//die;
        $customerApi = Mage::getModel('customapi/memberservice');
        $customerCrm=array(
          //   'member'=>array(
                'mobile' => '13761587298',
                'name' => 'test121',
                'email' =>'ahs1@qqq.com'
           //  )
        );
        $resultData=$customerApi->addMemberInfo($customerCrm);

        print_r($resultData);
        die('ok');
          /*  $customer = array();
          $customer['mobile'] = '13818367541';
         $erpCustomer = $customerApi->searchMembers($customer,1);
          print_r($erpCustomer);*/

        //$customerApi = Mage::getModel('customapi/memberservice');
        header('Content-Type: text/html; charset=UTF-8');
        //http://192.168.100.22:8090


        exit('over');

    }
   /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }
    
    
    protected function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    
   function searchMembersformAction()
    {
    	
    	$customer = array();
        $customer['mobile'] = '13600000001';
        $customer['memberID']='';
        $customer['name']='';
        $customer['tel']='';
        $customer['registerDate1']='';
        $customer['registerDate2']='';

    	$content = $this->getLayout()->createBlock('customapi/form', '', array(
            'attributes' => $customer,
            'action' => Mage::getUrl('customapi/testrun/searchMembers'),
            'name' => 'customer',
            'title'=>'Search members',
        ));

        $this->getResponse()->setBody($content->toHtml());
    	
    }
    
    
    function searchMembersAction()
    {



        echo '<head> <meta charset="UTF-8"><title>test api</title></head>';
        /* @var $customerApi D1m_Customapi_Model_Memberservice */
        $customerApi = Mage::getModel('customapi/memberservice');


        $params = $this->getRequest()->getParam('customer', array());
        echo 'method: searchMembers<br/>';

        $date = Mage::app()->getLocale()->date();
        $datestr = $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        echo 'post time: '.$datestr.'<br>';
        echo 'post data:<br/>';
        foreach($params as $key => $val)
        {
        	echo $key.': '.$val.'<br/>';
        }
        echo 'result:';
        echo '-------------------------<br/>';

        $data = $customerApi->searchMembers($params,1);
        // echo '<br/>';        var_dump( $data );
        
    }

    
    function addMemberformAction()
    {
    	
    	$customer = array();
        $customer['memberID'] = '';
        $customer['name'] = '';
        $customer['gender'] = '';
        $customer['mobile'] = '';
        $customer['tel'] = '';
        $customer['isValidMobile'] = '';
        $customer['email'] = '';
        $customer['isValidEmail'] = '';
        $customer['year'] = '';
        $customer['month'] = '';
        $customer['day'] = '';
        $customer['province'] = '';
        $customer['city'] = '';
        $customer['address'] = '';
        $customer['postcode'] = '';
        $customer['registerDate'] = '';
        $customer['registerCounter'] = '';
        $customer['belongToCounter'] = '';
        $customer['channel'] = '';
        $customer['tier'] = '';
        $customer['status'] = '';
        $customer['totalPoints'] = '';
        $customer['validPoints'] = '';
        $customer['classQuantity'] = '';
        $customer['firstOrderDate'] = '';
        $customer['knowFissler_TV'] = '';
        $customer['knowFisslerFromMagazine'] = '';
        $customer['knowFisslerFromNewsPaper'] = '';
        $customer['knowFisslerFromNetwork'] = '';
        $customer['knowFisslerFromFriends'] = '';
        $customer['knowFisslerFromOhter'] = '';
        $customer['familyStructure'] = '';
        $customer['occupation'] = '';
        $customer['annualIncome'] = '';
        $customer['purchaseFor'] = '';
        $customer['isAcceptSMS'] = '';
        $customer['isAcceptEmail'] = '';
        $customer['isAcceptPost'] = '';
        $customer['isInterestInNewArrival'] = '';
        $customer['isInterestInPromotion'] = '';
        $customer['isInterestInCookLesson'] = '';
        $customer['isInterestInSale'] = '';
        $customer['isInterestInAcativity'] = '';
        $customer['isInterestInCookware'] = '';
        $customer['isInterestInCutter'] = '';
        $customer['isInterestInCooker'] = '';
        $customer['isInterestInWineSet'] = '';
        $customer['isInterestInTableware'] = '';
        $customer['isInterestInChinaware'] = '';
        $customer['isInterestInOthers'] = '';
        $customer['requirements'] = '';
        $customer['createBy'] = '';
        $customer['createDate'] = '';
        $customer['modifyBy'] = '';
        $customer['modifyDate'] = '';
    	
    	$content = $this->getLayout()->createBlock('customapi/form', '', array(
            'attributes' => $customer,
            'action' => Mage::getUrl('customapi/testrun/addMember'),
            'name' => 'customer',
            'title'=>'Add member',
        ));

        $this->getResponse()->setBody($content->toHtml());
    	
    }
    
    function addMemberAction(){

        /* @var $customerApi D1m_Customapi_Model_Memberservice */
        $customerApi = Mage::getModel('customapi/memberservice');
        
        $customer = $this->getRequest()->getParam('customer', array());
        echo '<head> <meta charset="UTF-8"><title>test api</title></head>';
        echo 'method: addMember<br/>';

        $date = Mage::app()->getLocale()->date();
        $datestr = $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        echo 'post time: '.$datestr.'<br>';
        echo 'post data:<br/>';

        foreach($customer as $key => $val)
        {
        	echo $key.': '.$val.'<br/>';
        }

        echo 'result:';
        echo '-------------------------<br/>';
        
        $status = $customerApi->addMember($customer,1);
        
        echo '<br/>';
        
        if($status)
        {
        	echo 'success<br/>';
        }
        else
        {
        	echo 'fail<br/>';
        }
        echo '-------------------------<br/>';
    }
    
    
    function updateMemberformAction()
    {
    	$customer = array();
        $customer['memberID'] = '';
        $customer['name'] = 'liyuanyuan2';
        $customer['gender'] = '';
        $customer['mobile'] = '13600000001';
        $customer['tel'] = '';
        $customer['isValidMobile'] = '';
        $customer['email'] = '';
        $customer['isValidEmail'] = '';
        $customer['year'] = '';
        $customer['month'] = '';
        $customer['day'] = '';
        $customer['province'] = '';
        $customer['city'] = '';
        $customer['address'] = '';
        $customer['postcode'] = '';
        $customer['registerDate'] = '';
        $customer['registerCounter'] = '';
        $customer['belongToCounte'] = '';
        $customer['channel'] = '';
        $customer['tier'] = '';
        $customer['status'] = '';
        $customer['totalPoints'] = '';
        $customer['validPoints'] = '';
        $customer['classQuantity'] = '';
        $customer['firstOrderDate'] = '';
        $customer['knowFissler_TV'] = '';
        $customer['knowFisslerFromMagazine'] = '';
        $customer['knowFisslerFromNewsPaper'] = '';
        $customer['knowFisslerFromNetwork'] = '';
        $customer['knowFisslerFromFriends'] = '';
        $customer['knowFisslerFromOhter'] = '';
        $customer['familyStructure'] = '';
        $customer['occupation'] = '';
        $customer['annualIncome'] = '';
        $customer['purchaseFor'] = '';
        $customer['isAcceptSMS'] = '';
        $customer['isAcceptEmail'] = '';
        $customer['isAcceptPost'] = '';
        $customer['isInterestInNewArrival'] = '';
        $customer['isInterestInPromotion'] = '';
        $customer['isInterestInCookLesson'] = '';
        $customer['isInterestInWineTesting'] = '';
        $customer['isInterestInSale'] = '';
        $customer['isInterestInAcativity'] = '';
        $customer['isInterestInCookware'] = '';
        $customer['isInterestInCutter'] = '';
        $customer['isInterestInCooker'] = '';
        $customer['isInterestInWineSet'] = '';
        $customer['isInterestInTableware'] = '';
        $customer['isInterestInChinaware'] = '';
        $customer['isInterestInOthers'] = '';
        $customer['requirements'] = '';
        $customer['createBy'] = '';
        $customer['createDate'] = '';
        $customer['modifyBy'] = '';
        $customer['modifyDate'] = '';
    	
    	$content = $this->getLayout()->createBlock('customapi/form', '', array(
            'attributes' => $customer,
            'action' => Mage::getUrl('customapi/testrun/updateMember'),
            'name' => 'customer',
            'title'=>'Update member',
        ));

        $this->getResponse()->setBody($content->toHtml());
    	
    }
    
    function updateMemberAction(){

        /* @var $customerApi D1m_Customapi_Model_Memberservice */
        $customerApi = Mage::getModel('customapi/memberservice');
        
         $customer = $this->getRequest()->getParam('customer', array());
        echo '<head> <meta charset="UTF-8"><title>test api</title></head>';
        echo 'method: updateMember<br/>';

        $date = Mage::app()->getLocale()->date();
        $datestr = $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        echo 'post time: '.$datestr.'<br>';

        echo 'post data:<br/>';

        foreach($customer as $key => $val)
        {
        	echo $key.': '.$val.'<br/>';
        }

        echo 'result:';
        echo '-------------------------<br/>';
        
        $status = $customerApi->updateMember($customer,1);
        
        echo '<br/>';
        
        if($status)
        {
        	echo 'success<br/>';
        }
        else
        {
        	echo 'fail<br/>';
        }
        echo '-------------------------<br/>';
        
    }
    
    function addClassOrderformAction()
    {
    	
    	 $classOrder = array();
    	 $classOrder['MemberID'] = '';
    	 $classOrder['Mobile'] = '';
    	 $classOrder['PurchaseDate'] = '';
    	 $classOrder['Quantity'] = '';
    	 $classOrder['Price'] = '';
    	 $classOrder['Points'] = '';
    	 $classOrder['TotalPrice'] = '';
    	 $classOrder['TotalPoints'] = '';
    	 $classOrder['StartDate'] = '';
    	 $classOrder['ExpireDate'] = '';
    	 $classOrder['PromotionCode'] = '';
    	 $classOrder['Channel'] = '';
    	 $classOrder['CreateBy'] = '';
    	 $classOrder['CreateDate'] = '';
    	 $classOrder['ModifyBy'] = '';
    	 $classOrder['ModifyDate'] = '';
    	
    	$content = $this->getLayout()->createBlock('customapi/form', '', array(
            'attributes' => $classOrder,
            'action' => Mage::getUrl('customapi/testrun/addClassOrder'),
            'name' => 'classorder',
            'title'=>'Add class order',
        ));

        $this->getResponse()->setBody($content->toHtml());
    	
    }


    public function getProduct($sku)
    {
        $id = Mage::getModel('catalog/product')->getIdBySku($sku);

        if ($id)
        {
            return Mage::getModel('catalog/product')->load($id);
        }

        return ;
    }
    function addClassOrderAction(){

        /* @var $customerApi D1m_Customapi_Model_Orderservice */

        $orderId='200000220';//'200000509';//
         $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
        $customerApi = Mage::getModel('customapi/orderservice');
        $customerApi->addClassOrder($order,1);

        die;
        $classOrder = $this->getRequest()->getParam('classorder', array());
        echo '<head> <meta charset="UTF-8"><title>test api</title></head>';
        echo 'method: addClassOrder<br/>';
        $date = Mage::app()->getLocale()->date();
        $datestr = $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        echo 'post time: '.$datestr.'<br>';
        echo 'post data:<br/>';

        foreach($classOrder as $key => $val)
        {
        	echo $key.': '.$val.'<br/>';
        }

        echo 'result:';
        echo '-------------------------<br/>';
        
        $status = $customerApi->addClassOrder($classOrder,1);
        
        if($status)
        {
        	echo 'success';
        }
        else
        {
        	echo 'fail';
        }
    }
    
    
    function addReserveClassformAction()
    {
    	
    	 $classOrder = array();
    	 $classOrder['MemberID'] = '';
    	 $classOrder['ReserveDate'] = '';
    	 $classOrder['ClassDate'] = '';
    	 $classOrder['Type'] = '';
    	 $classOrder['ClassName'] = '';
    	 $classOrder['Quantity'] = '';
    	 $classOrder['CreateBy'] = '';
    	 $classOrder['CreateDate'] = '';
    	 $classOrder['CreateDate'] = '';
    	 $classOrder['ModifyBy'] = '';
    	 $classOrder['ModifyDate'] = '';
    	
    	$content = $this->getLayout()->createBlock('customapi/form', '', array(
            'attributes' => $classOrder,
            'action' => Mage::getUrl('customapi/testrun/addReserveClass'),
            'name' => 'classorder',
            'title'=>'Add reserve class',
        ));

        $this->getResponse()->setBody($content->toHtml());
    	
    }
    
    
        
    function addReserveClassAction(){

        /* @var $customerApi D1m_Customapi_Model_Orderservice */
        $customerApi = Mage::getModel('customapi/orderservice');
        
        $classOrder = $this->getRequest()->getParam('classorder', array());
        echo '<head> <meta charset="UTF-8"><title>test api</title></head>';
        echo 'method: addReserveClass';
        $date = Mage::app()->getLocale()->date();
        $datestr = $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        echo 'post time: '.$datestr.'<br>';
        echo 'post data:<br/>';

        foreach($classOrder as $key => $val)
        {
        	echo $key.': '.$val.'<br/>';
        }

        echo 'result:';
        echo '-------------------------<br/>';
        
        $status = $customerApi->addReserveClass($classOrder,1);
        
        if($status)
        {
        	echo 'success';
        }
        else
        {
        	echo 'fail';
        }
        
    }
    
    
    function pointsformAction()
    {
    	
    	 $classOrder = array();
    	 $classOrder['MemberID'] = '';
    	 $classOrder['Mobile'] = '';
    	 $classOrder['Type'] = '';
    	 $classOrder['Property'] = '';
    	 $classOrder['RedemptionDate'] = '';
    	 $classOrder['Points'] = '';
    	 $classOrder['PreviousPoints'] = '';
    	 $classOrder['Channel'] = '';
    	 $classOrder['Remark'] = '';
    	 $classOrder['Remark'] = '';
    	 $classOrder['CreateDate'] = '';
    	 $classOrder['ModifyBy'] = '';
    	 $classOrder['ModifyDate'] = '';

    	
    	$content = $this->getLayout()->createBlock('customapi/form', '', array(
            'attributes' => $classOrder,
            'action' => Mage::getUrl('customapi/testrun/updateMember'),
            'name' => 'customer',
            'title'=>'Points',

        ));

        $this->getResponse()->setBody($content->toHtml());
    	
    }
    
}