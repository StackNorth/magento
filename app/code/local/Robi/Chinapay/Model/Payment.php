<?php

class Robi_Chinapay_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    protected $_code  = 'chinapay_payment';
    protected $_formBlockType = 'chinapay/form';

    // Alipay return codes of payment
    const RETURN_CODE_ACCEPTED      = 'Success';
    const RETURN_CODE_TEST_ACCEPTED = 'Success';
    const RETURN_CODE_ERROR         = 'Fail';

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
      * Returns Chinapay Public Key URL
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
		return Mage::getUrl('checkout/onepage/success', array('_secure' => true));
	}

	/**
	 *  Return URL for Chinapay success response
	 *
	 *  @return	  string URL
	 */
	protected function getSuccessURL()
	{
		return Mage::getUrl('chinapay/payment/success', array('_secure' => true));
	}
	
    /**
     *  Return URL for Chinapay failure response
     *
     *  @return	  string URL
     */
    protected function getErrorURL()
    {
        return Mage::getUrl('chinapay/payment/error', array('_secure' => true));
    }

	/**
	 *  Return URL for Chinapay notify response
	 *
	 *  @return	  string URL
	 */
	protected function getNotifyURL()
	{
		return Mage::getUrl('chinapay/payment/ueronotify/', array('_secure' => true));
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
     *  Form block description
     *
     *  @return	 object
     */
    public function createFormBlock($name)
    {
        $block = $this->getLayout()->createBlock('chinapay/form_payment', $name);
        $block->setMethod($this->_code);
        $block->setPayment($this->getPayment());

        return $block;
    }

    /**
     *  Return Order Place Redirect URL
     *
     *  @return	  string Order Redirect URL
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('chinapay/payment/redirect');
    }
    
    
    public function getStandardCheckoutFormFields()
    {
        $session = Mage::getSingleton('checkout/session');
        
        $order 		 = $this->getPayment();
        $transId	 = $order->getIncrementId();
        
        $converted_final_price 	= $amount = sprintf('%.2f', $order->getBaseGrandTotal());
        
        // $converted_final_price = 0.01;
        
        require_once( Mage::getBaseDir().DIRECTORY_SEPARATOR."netpay_keys".DIRECTORY_SEPARATOR ."netpayclient.php");
		
		$model  = Mage::getModel("chinapay/payment");
	  	$pgPubk = Mage::getBaseDir().DIRECTORY_SEPARATOR ."netpay_keys".DIRECTORY_SEPARATOR .$model->getConfigData('public_key');
	  	$merPrk = Mage::getBaseDir().DIRECTORY_SEPARATOR."netpay_keys".DIRECTORY_SEPARATOR .$model->getConfigData('private_key');
		
		$merid = buildKey($merPrk);
		if(!$merid) {
			Mage::throwException("导入私钥文件失败！");
			exit;
		}
	  	
	  	$priv1 		= $transId;
	  	
	  	$ordid 		= sprintf('%016d', $transId);
	  	
	  	$merid 		= $this->getPartnerId();
	  	
	  	$transamt 	= sprintf('%012d',sprintf('%.2f', $converted_final_price)*100);
	  	$curyid 	= '156';
	  	$transdate 	= date('Ymd');
	  	$transtype 	= '0001';
	  	
	  	
	  	$plain = $merid . $ordid . $transamt . $curyid . $transdate . $transtype . $priv1;
		//生成签名值，必填
		$chkvalue = sign($plain);
	  	
        $parameter= array(
	        			'MerId'		=> $merid,//商户号,15位长度
	        			'OrdId'		=> $ordid,//订单号,16位长度
	        			'TransAmt'	=> $transamt,//订单总金额,订单交易金额，12位长度，左补0,必填,单位为分
	        			'CuryId'	=> $curyid,//人民币
	        			'TransDate'	=> $transdate,   //订单交易日期，8位长度
	        			'TransType'	=> $transtype,  //0001付款交易
	        			'Priv1'		=> $priv1,  //memo
	        			'Version'	=>$this->getGatewayVersion(),
	        			'BgRetUrl'  => $this->getNotifyURL(),//后台交易接收URL，长度不要超过80个字节
	        			'PageRetUrl'=> $this->getSuccessURL(),//页面交易接收URL，长度不要超过80个字节
	        			'ChkValue'	=> $chkvalue
        			);
        
        return $parameter;
    }

	public function charset_encode($input,$_output_charset ,$_input_charset ="GBK" ) {
		$output = "";
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
		
        foreach (Mage::getConfig()->getNode('global/payment/chinapay_payment/languages')->asArray() as $data) 
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
        return 'failed';
    }

}