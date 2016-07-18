<?php

class Robi_Msgnotice_SmsController extends Mage_Core_Controller_Front_Action
{

    public function preDispatch()
    {
        parent::preDispatch();

        return $this;
    }


	public function sendtextsmsAction()
	{
		$phone  = '18616509901';
		$text   = '这是一条测试短信，this is a test sms。';
		
		try
		{
			$status = Mage::getModel('robi_checkout/msg')->sendMsg($phone,$text);
			if($status)
				echo '已发送！';
			else
				echo '发送失败！';
		}
		catch(Exception $e)
		{
			Mage::printException($e);
		}
		
		
	}

   

}

