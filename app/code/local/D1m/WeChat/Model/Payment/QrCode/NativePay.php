<?php
/**
 * 
 * 刷卡支付实现类
 * @author widyhu
 *
 */
class D1m_WeChat_Model_Payment_QrCode_NativePay extends Mage_Core_Model_Abstract
{
	/**
	 * 
	 * 生成扫描支付URL,模式一
     *
	 * @param BizPayUrlInput $bizUrlInfo
	 */
	public function GetPrePayUrl($productId)
	{
		$biz = new D1m_WeChat_Model_Payment_QrCode_BizPayUrl();
		$biz->SetProduct_id($productId);
		$values = D1m_WeChat_Model_Payment_Util_PayApi::bizpayurl($biz);
		$url    = D1m_WeChat_Model_Payment::BIZ_PAY_URL . $this->ToUrlParams($values);
		return $url;
	}
	
	/**
	 * 
	 * 参数数组转换为url参数
	 * @param array $urlObj
	 */
	private function ToUrlParams($urlObj)
	{
		$buff = "";
		foreach ($urlObj as $k => $v)
		{
			$buff .= $k . "=" . $v . "&";
		}
		
		$buff = trim($buff, "&");
		return $buff;
	}
	
	/**
	 * 
	 * 生成直接支付url，支付url有效期为2小时,模式二
     *
	 * @param D1m_WeChat_Model_Payment_Util_UnifiedOrder $input
	 */
	public function GetPayUrl($input)
	{
        /* @var $api  D1m_WeChat_Model_Payment_Util_PayApi */
        $api = Mage::getModel('weChat/payment_util_payApi');
        $input->SetTrade_type(D1m_WeChat_Model_Payment_Type::TRADE_TYPE_NATIVE);

        $result = $api->unifiedOrder($input);

        if (isset($result['err_code']) && isset($result['err_code_des']))
        {
            D1m_WeChat_Model_PromptMessage::getInstance()->setMessage($result['err_code_des']);
        }

        return $result;
	}
}