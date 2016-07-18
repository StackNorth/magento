<?php
class Robi_Checkout_Helper_Msg extends Mage_Core_Helper_Abstract
{	
	const MSG_API_URL = 'http://fissler.webpowerchina.cn/sms/rest/v1/sms';
	const MSG_USERNAME = 'admin';
	const MSG_PASSWORD = 'gah4OBeiNg';
	const MSG_USERAGENT = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1';
	
	
	public function sendVerifyMsg($phone, $verify_code)
	{
	  //$content = sprintf('感谢您访问Fissler Academy。您的验证码为：{%s}，请与 {%s}分钟内操作。', $verify_code, 1);
		$content = sprintf('感谢您访问Fissler Academy。您的验证码为：%s，请于 %s分钟内操作。', $verify_code, 1);
		//echo '$content:'.$content.'<br/>';
		
		return $this->sendMsg2($phone, $content);
	}
	
	public function sendOrderMsg($phone, $classname, $classdate, $classplace, $persons)
	{
        /*
验证模板：
感谢您访问Fissler Academy。您的验证码为：{验证码}，请与 {}分钟内操作。
购买课程模板：
您已成功预约{课程名称}课程，上课时间为{课程时间}，地点{地址}，人数{}人，感谢您的参与
        */
        settype($persons,"integer"); //转成整数
        $content='您已成功预约'.$classname.'课程，上课时间为'.$classdate.'，地点'.$classplace.'，人数'.$persons.'人，感谢您的参与。';
        //根据系统配置是否发送短信
        $dosend = Mage::getStoreConfig('robi_checkout/general/dosend');
        if ($dosend!='1')
        {
            Mage::log($content);
            return true;
        }
	    return $this->sendMsg($phone, $content);
	}
	
	public function sendMsg($phone, $msg)
	{
		 //$_postFields  = array('mobile'=>$phone,'content'=>$msg, 'campaignID'=> 1);
		 $_postFields  = array('mobile'=>$phone,'content'=>$msg);
		 
		 $s = curl_init(); 
         curl_setopt($s,CURLOPT_URL, Robi_Checkout_Helper_Msg::MSG_API_URL); 

         curl_setopt($s,CURLOPT_HTTPHEADER,array("Content-Type: application/json","X-HTTP-Method-Override: POST","Authorization: Basic " . base64_encode(Robi_Checkout_Helper_Msg::MSG_USERNAME.":" . Robi_Checkout_Helper_Msg::MSG_PASSWORD)));
         
         curl_setopt($s, CURLOPT_POST, true); 
         curl_setopt($s, CURLOPT_POSTFIELDS, json_encode($_postFields)); 
		 
		 curl_setopt($s,CURLOPT_USERAGENT,Robi_Checkout_Helper_Msg::MSG_USERAGENT);
		 
		 curl_setopt($s,CURLOPT_TIMEOUT, 30); 
         curl_setopt($s,CURLOPT_MAXREDIRS,4); 
         curl_setopt($s,CURLOPT_RETURNTRANSFER,true); 
		 
         $_webpage = curl_exec($s); 
         $_status  = curl_getinfo($s,CURLINFO_HTTP_CODE);
         curl_close($s);
         Mage::log('$phone:'.$phone.'; $msg: '.$msg.'; $_webpage:'.$_webpage.'; $_status : '.$_status, null, 'msg.log', $forceLog = true);
         
         if($_status == 200)
         {
         	$content = json_decode($_webpage,true);
         	if(isset($content['status']) && $content['status'] == 'OK')
         		return true;
         	else
         		return false;
         }
         return false;
		
	}

    public function sendMsg2($phone, $msg)
    {
        //成功时返回ok
        //否则返回失败信息

        //$_postFields  = array('mobile'=>$phone,'content'=>$msg, 'campaignID'=> 1);
        $_postFields  = array('mobile'=>$phone,'content'=>$msg);

        $s = curl_init();
        curl_setopt($s,CURLOPT_URL, Robi_Checkout_Helper_Msg::MSG_API_URL);

        curl_setopt($s,CURLOPT_HTTPHEADER,array("Content-Type: application/json","X-HTTP-Method-Override: POST","Authorization: Basic " . base64_encode(Robi_Checkout_Helper_Msg::MSG_USERNAME.":" . Robi_Checkout_Helper_Msg::MSG_PASSWORD)));

        curl_setopt($s, CURLOPT_POST, true);
        curl_setopt($s, CURLOPT_POSTFIELDS, json_encode($_postFields));

        curl_setopt($s,CURLOPT_USERAGENT,Robi_Checkout_Helper_Msg::MSG_USERAGENT);

        curl_setopt($s,CURLOPT_TIMEOUT, 30);
        curl_setopt($s,CURLOPT_MAXREDIRS,4);
        curl_setopt($s,CURLOPT_RETURNTRANSFER,true);

        $_webpage = curl_exec($s);
        $_status  = curl_getinfo($s,CURLINFO_HTTP_CODE);
        curl_close($s);
        Mage::log('$phone:'.$phone.'; $msg: '.$msg.'; $_webpage:'.$_webpage.'; $_status : '.$_status, null, 'msg.log', $forceLog = true);

        if($_status == 200)
        {
            $content = json_decode($_webpage,true);
            if (isset($content['status']))
            {
                if( $content['status'] == 'OK')
                    return 'ok';
                $status=$content['status'];
                //{"status":"ERROR:1013:[Invalid messageID]"}
                if (substr($status,0,6)=='ERROR:')
                {
                    $arr=explode(':',$status);
                    $code=$arr[1];
                    settype($code,"integer");
                    if ($code==1002)
                        return '手机号码格式有误,请修改后重试';
                }
            }

        }
        return '系统繁忙，请稍后再试';

    }


	
	
}
