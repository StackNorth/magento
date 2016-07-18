<?php
class D1m_Core_Helper_String extends Mage_Core_Helper_String
{
    //以JSON提示为准的信息提示
    const  JSON_MESSAGE_STATUS_SUCCESS = 'success';
    const  JSON_MESSAGE_STATUS_FAILED  = 'failed';


    //字符串验证
    public function isChinese($string, $charset = self::ICONV_CHARSET)
    {
        $charset && $charset != self::ICONV_CHARSET and $string = iconv($charset, self::ICONV_CHARSET . '//IGNORE', $string);
        return preg_match('/^[\p{Han}]+$/u', $string);
    }



    //validate and get string
    /**
     * 根据邮箱地址来获得邮箱的名称
     */
    public  function getNameBaseEmailAddress($email_address){
        $username  = '';
        $customerSession     = $this->getCustomerSession();
        if($customerSession && $customerSession->getCustomer()){
            $customer= $customerSession->getCustomer();

            if (strlen(trim($customer->getUsername()))){
                $username = $customer->getUsername();
            }else {
                $pattern = '/(?<usename>[-\w\d_+.]+[^.])@[\w\d._]+/isu';
                if(preg_match($pattern,$email_address, $matches)) $username= $matches['usename'];
            }
        }
        return $username;
    }

    /**
     * 将数组转换成JSON,并且以application/json 的MIME输出
     * */
    public function convertToJson($dataArray=array()){
        $result = Mage::helper('core')->jsonEncode($dataArray);
        Mage::app()->getResponse()->setHeader('Content-type', 'application/json');
        Mage::app()->getResponse()->setBody($result);
    }

    // json的信息提示
    public function outputJsonMessage($status=self::JSON_MESSAGE_STATUS_SUCCESS,$message=array()){
        if($status==self::JSON_MESSAGE_STATUS_SUCCESS){
            $messageStatus = self::JSON_MESSAGE_STATUS_SUCCESS;
        }elseif($status==self::JSON_MESSAGE_STATUS_FAILED){
            $messageStatus = self::JSON_MESSAGE_STATUS_FAILED;
        }

        $result  = array(
            'status'    =>  $messageStatus,
            'messages' =>   $message
        );

        $this->convertToJson($result);
    }

    /**
     *  得到当前用户的session
     */
    private function getCustomerSession(){
        $session = Mage::getSingleton('customer/session');
        return $session;
    }
}
