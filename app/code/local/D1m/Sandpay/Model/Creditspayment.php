<?php

class D1m_Sandpay_Model_Creditspayment extends Mage_Payment_Model_Method_Abstract
{
    protected $_code  = 'sandpay_payment';
    protected $_formBlockType = 'sandpay/form';


    // Payment configuration
    protected $_isGateway               = false;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;

    // Order instance
    protected $_order = null;
    
    /**
     * Returns Partner Id
     * 
     */
     public function getPartnerId()
     {
     	$partnerId=$this->getConfigData('partner_id');
     	return $partnerId;
     }

    /**
     *  Returns Target URL
     *
     *  @return	  string Target URL
     */
    public function getGatewayUrl()
    {
        return $this->getConfigData('gateway');
    }
    
    /**
     * Returns gateway version
     * 
     */
     public function getGatewayVersion()
     {
     	$version=$this->getConfigData('gateway_version');
     	return $version;
     }
     
     /**
      * Returns Private Key URL
      * 
      */
      public function getPrivateKey()
      {
      	$privateKey=$this->getConfigData('private_key');
      	return $privateKey;
      }
      
      /**
      * Returns Sandpay Public Key URL
      * 
      */
      public function getPublicKey()
      {
      	return $this->getConfigData('public_key');
      }
      
    /**
     *  Return back URL
     *
     *  @return	  string URL
     */
	protected function getReturnURL()
	{
		return Mage::getUrl('credits/checkout/success', array('_secure' => true));
	}

	/**
	 *  Return URL for Sandpay success response
	 *
	 *  @return	  string URL
	 */
	protected function getSuccessURL()
	{
		return Mage::getUrl('sandpay/creditpayment/success', array('_secure' => true));
	}
	
    /**
     *  Return URL for Sandpay failure response
     *
     *  @return	  string URL
     */
    protected function getErrorURL()
    {
        return Mage::getUrl('sandpay/creditpayment/error', array('_secure' => true));
    }

	/**
	 *  Return URL for Sandpay notify response
	 *
	 *  @return	  string URL
	 */
	protected function getNotifyURL()
	{
		return Mage::getUrl('sandpay/creditpayment/notify', array('_secure' => true));
	}
	
    /**
     * Capture payment
     *
     * @param   Varien_Object $orderPayment
     * @return  Mage_Payment_Model_Abstract
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $payment->setStatus(self::STATUS_APPROVED)
            ->setLastTransId($this->getTransactionId());

        return $this;
    }

    /**
     *  Return Order Place Redirect URL
     *
     *  @return	  string Order Redirect URL
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('sandpay/creditpayment/redirect');
    }
    
    
    public function getStandardCheckoutFormFields()
    {

        $order 		 = $this->getPayment();
        $transId	 = $order->getId();
        require_once Mage::getBaseDir()."/sandpay/SandPayUtil.php";



        $debugmodel = SD_USE_MODEL;

        $model  = Mage::getModel("sandpay/payment");

        $sand_plug = new SandPayUtil($debugmodel);
        $sandPublicPath =Mage::getBaseDir().DIRECTORY_SEPARATOR ."sandpay".DIRECTORY_SEPARATOR .$model->getConfigData('public_key');
        $merPrivatePath = Mage::getBaseDir().DIRECTORY_SEPARATOR."sandpay".DIRECTORY_SEPARATOR .$model->getConfigData('private_key');

        $merchant_id =$model->getConfigData('partner_id');; //"666001041310001";
        $merchant_name=$model->getConfigData('merchant_name'); // 'fissleracademy';
        $amount=$order->getGrandTotal();


        if ($merchant_id=="") die('没有定义商户id,请检查支付方式配置');
        if ($sandPublicPath=="") die('没有定义公钥文件,请检查支付方式配置');
        if ($merPrivatePath=="") die('没有定义私钥文件,请检查支付方式配置');

        $order_amount=sprintf('%012d',$amount*100);
        $order_id= $transId;
        $prefix_order=$model->getConfigData('prefix_creditorder');
        $order_id=$prefix_order.$order_id;

        $version='01';
        $charset='UTF-8';
        $trans_type='0001';
        $order_time=date("YmdHis");
        $currency='156';
        $fronturl=Mage::getUrl('sandpay/creditpayment/success');
        $backurl=Mage::getUrl('sandpay/creditpayment/notify');
        $goods_content='';
        $custom_ip='';
        $signOrg="version=$version&charset=$charset&trans_type=$trans_type&merchant_id=$merchant_id&merchant_name=$merchant_name&goods_content=$goods_content&custome_ip=$custom_ip";
        $signOrg=$signOrg."&order_id=$order_id&sm_billno=&order_time=$order_time&order_amount=$order_amount&currency=$currency&pay_kind=&front_url=$fronturl&back_url=$backurl&merchant_attach=";

        $load_result = $sand_plug->LoadKeyFile($merchant_id, $merPrivatePath, $sandPublicPath);
        if (!$load_result) {Mage::throwException("导入密钥文件失败！"); exit;}

        $sign_type='00';
        $plainText =$signOrg;
        $sign = $sand_plug->Sign($plainText);
        $parameter= array(
            "version"=>$version,
            "charset"=>$charset,
            "trans_type"=>$trans_type,
            "merchant_id"=>$merchant_id,
            "merchant_name"=>$merchant_name,
            "goods_content"=>$goods_content,
            "custome_ip"=>$custom_ip,
            "order_id" =>$order_id,
            "sm_billno" =>"",
            "order_time"=>$order_time,
            "order_amount"=>$order_amount,
            "currency"=>$currency,
            "pay_kind"=>"",
            "front_url"=>$fronturl,
            "back_url"=>$backurl,
            "merchant_attach"=>"",
            "sign_type"=>$sign_type,
            "sign"=>$sign,
        );

        return $parameter;

    }

	public function charset_encode($input,$_output_charset ,$_input_charset ="GBK" ) {
		//$output = "";
		if($_input_charset == $_output_charset || $input ==null) {
			$output = $input;
		} elseif (function_exists("mb_convert_encoding")){
			$output = mb_convert_encoding($input,$_output_charset,$_input_charset);
		} elseif(function_exists("iconv")) {
			$output = iconv($_input_charset,$_output_charset,$input);
		} else die("sorry, you have no libs support for charset change.");
		return $output;
	}
   
	/**
	 * Return authorized languages by Alipay
	 *
	 * @param	none
	 * @return	array
	 */
	protected function _getAuthorizedLanguages()
	{
		$languages = array();
		
        foreach (Mage::getConfig()->getNode('global/payment/sandpay_payment/languages')->asArray() as $data) 
		{
			$languages[$data['code']] = $data['name'];
		}
		
		return $languages;
	}
	
	/**
	 * Return language code to send to Alipay
	 *
	 * @param	none
	 * @return	String
	 */
	protected function _getLanguageCode()
	{
		// Store language
		$language = strtoupper(substr(Mage::getStoreConfig('general/locale/code'), 0, 2));

		// Authorized Languages
		$authorized_languages = $this->_getAuthorizedLanguages();

		if (count($authorized_languages) === 1) 
		{
			$codes = array_keys($authorized_languages);
			return $codes[0];
		}
		
		if (array_key_exists($language, $authorized_languages)) 
		{
			return $language;
		}
		
		// By default we use language selected in store admin
		return $this->getConfigData('language');
	}

    public function generateErrorResponse()
    {
        die($this->getErrorResponse());
    }

    public function getSuccessResponse()
    {
        return 'success';
    }


    public function getErrorResponse()
    {
        return 'fail';
    }

}