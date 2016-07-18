<?php

include_once("DESTool.php");
include_once("TripleDESTool.php");
include_once("RSAHelpImpl.php");
/**
* PHP 支付插件工具接口
*
* @author Nano
*/
class SandPayUtil {
	
	//指定插件模式
	private $plug_model = SD_USE_MODEL;
	
	private $rsa_help ;
	
	private $des_tool ;
	
	private $triple_tools ;
	
	private $LastError ;
	
	private $sd_default_key_arr = array("DEBUG"   => array("SD_DEFAULT_PUBLIC_EXPONENT" => "65537",
										                   "SD_DEFAULT_MODULUS" => "D2F64C5D15BF54288281CFEAF37E949F39FB678E8BEA5936F6D22E47DA0516DC00C02C8B5BE413013FCBEAB563C57E697C81199BB9544E2047C341453BA57E1101F85DBD17BB1503B1D1E77496D168A7C89D7EC6A8C46A2755F3F9C2E92FD1817D2EDD66A94C0AB66F8932D2D230B40FEEC08F6C73391490867C7B7A7BCA8335"),
			                            "DEVELOP" => array("SD_DEFAULT_PUBLIC_EXPONENT" => "65537",
										                   "SD_DEFAULT_MODULUS" => "D2F64C5D15BF54288281CFEAF37E949F39FB678E8BEA5936F6D22E47DA0516DC00C02C8B5BE413013FCBEAB563C57E697C81199BB9544E2047C341453BA57E1101F85DBD17BB1503B1D1E77496D168A7C89D7EC6A8C46A2755F3F9C2E92FD1817D2EDD66A94C0AB66F8932D2D230B40FEEC08F6C73391490867C7B7A7BCA8335"),
			                            "PRODUCT" => array("SD_DEFAULT_PUBLIC_EXPONENT" => "65537",
					                                       "SD_DEFAULT_MODULUS" => "B6D363BFE1EDB743C20E4CCF09CE452E00E23FD2C20B0645A477D4CEAF01992B4585D44F4DB043784E2F2A8A673FF63A83B973EB817B169D892E4AA3118E74E857218087378D37386FEE01498E3C787DD56B3E90B9A3A169220DD2B6D0B35A5D2D48963C3D20ABF2AAA48916A0E106C7569BE232C63C5FC5E83F0D5E24313DCF")
									);
	
	private $sd_key_coll = array("desKey" => "","pubKey" => "","privKey" => "");
	
	function __construct($model) 
	{
		$this->rsa_help = new RSAHelpImpl();
		$this->des_tool = new DESTool();
		$this->triple_tools = new TripleDESTool();
		$this->plug_model = $model;
	}

	function GetKey($key){
		return $this->sd_key_coll[$key];
	}
	
	/**
	 * get last error info
	 * 
	 */
	public  function  GetLastError() 
	{
	     return $this->LastError;
	}
	
	/**
	 *  load acq key
	 *  
	 *  @param $acqPrivatePath acq prikey path
	 *  @param $acqSandPublicPath acq pubkey path
	 *  @return boolean true/false load result
	 */
	public function LoadAcqKeyFile($acqPrivatePath,$acqSandPublicPath)
	{
		$load_result = false;
		try{
			$pubkey_read_fileHandle = fopen($acqSandPublicPath, "r");
				
			$privkey_read_fileHandle = fopen($acqPrivatePath, "r");
				
			$pubkey_info  = fread($pubkey_read_fileHandle, filesize($acqSandPublicPath));
				
			$privkey_info =  fread($privkey_read_fileHandle, filesize($acqPrivatePath));
				
			fclose($privkey_read_fileHandle);
				
			fclose($pubkey_read_fileHandle);
				
			//get publickey
			$this->sd_key_coll['pubKey'] = $pubkey_info;
				
			//get privatekey
			$this->sd_key_coll['privKey'] = $privkey_info;
				
			$load_result = true;
		}catch (Exception $e){
			$this->LastError = "加载密钥过程错误";
			error_log(date("[Y-m-d H:i:s]")." -[ error : " .$this->LastError. "\n", 3, "/tmp/sd_plug_err.log");
		}
		return $load_result;
	}
	
	/**
	 *  load merchant key
	 *  
	 *  @param $merId merId
	 *  @param $merPrivatePath pkey path
	 *  @param $sandPublicPath pubkey path
	 *  @return boolean true/false load result
	 */
	public function LoadKeyFile($merId,$merPrivatePath,$sandPublicPath)
	{
		$load_result = false;
		try{
			$pubkey_read_fileHandle = fopen($sandPublicPath, "r");
			
			$privkey_read_fileHandle = fopen($merPrivatePath, "r");
			
			$pubkey_info  = fread($pubkey_read_fileHandle, filesize($sandPublicPath));
			
			$privkey_info =  fread($privkey_read_fileHandle, filesize($merPrivatePath));
			
			fclose($privkey_read_fileHandle);
			
			fclose($pubkey_read_fileHandle);
			
			//get publickey
			$this->sd_key_coll['pubKey'] = $pubkey_info;
			
			//get privatekey
			$this->sd_key_coll['privKey'] = $privkey_info;
			
			$load_result = true;
		}catch (Exception $e){
			$this->LastError = "加载密钥过程错误";
			error_log(date("[Y-m-d H:i:s]")." -[ error : " .$this->LastError. "\n", 3, "/tmp/sd_plug_err.log");	
		}
		return $load_result;
	}
	
	/**
	 *  gene workkey
	 *  
	 *  @return boolean true/false gen work key result 
	 */
	public function GenWorkKey()
	{
		$gen_result = false;
		try{
			$this->sd_key_coll["desKey"] = $this->triple_tools->genTripleDES();
			$gen_result = true;
		}catch (Exception $e){
			$this->LastError = "生成工作密钥错误";
			error_log(date("[Y-m-d H:i:s]")." -[ error : " .$this->LastError. "\n", 3, "/tmp/sd_plug_err.log");
		}
		return $gen_result;
	}
	
	public function SetWorkkey($desKey){
		$this->sd_key_coll["desKey"] = $desKey;
	}
	
	/**
	 * rsa encrypt workkey
	 * 
	 * @return  string encrypt workkey data  
	 */
	public function EncryptWorkKey()
	{
		try{
			return $this->rsa_help->encryptByPublic($this->sd_key_coll["desKey"], $this->sd_key_coll["pubKey"]);
		}catch (Exception $e){
			$this->LastError = "加密工作密钥错误";
			error_log(date("[Y-m-d H:i:s]")." -[ error : " .$this->LastError. "\n", 3, "/tmp/sd_plug_err.log");
		}
	}
	
	/**
	 * rsa decrypt workkey
	 * 
	 * @param $cipherText 
	 * 
	 * @return boolean  decrypt workkey reslut
	 */
	public function DecryptWorkKey($cipherText)
	{
		try{
			$this->sd_key_coll["desKey"] = $this->rsa_help->decryptByPrivate($cipherText, $this->sd_key_coll["privKey"]);
			if(!empty($this->sd_key_coll["desKey"])){
				return true;
			}else{
				return false;
			}
		}catch (Exception $e){
			$this->LastError = "解密工作密钥错误";
			error_log(date("[Y-m-d H:i:s]")." -[ error : " .$this->LastError. "\n", 3, "/tmp/sd_plug_err.log");
		}
		return false;
	}
	
	/**
	 * 3des encrypt data
	 * 
	 * @param unknown_type $plainText
	 * 
	 * @return string encrypt data
	 */
	public function EncryptData($plainText)
	{
		try{
			return $this->triple_tools->encrypt($plainText, $this->sd_key_coll["desKey"]);
 		}catch (Exception $e){
 			$this->LastError = "3DES加密数据错误";
 			error_log(date("[Y-m-d H:i:s]")." -[ error : " .$this->LastError. "\n", 3, "/tmp/sd_plug_err.log");
 		}	
	}
	
	/**
	 * 3des decrypt data
	 * 
	 * @param unknown_type $cipherText
	 * 
	 * @return string decrypt data
	 */
	public function DecryptData($cipherText){
		try{
			return $this->triple_tools->decrypt($cipherText, $this->sd_key_coll["desKey"]);
		}catch (Exception $e){
			$this->LastError = "3DES解密数据错误";
			error_log(date("[Y-m-d H:i:s]")." -[ error : " .$this->LastError. "\n", 3, "/tmp/sd_plug_err.log");
		}
	}
	
	/**
	 * RSA sign data
	 *
	 * @param unknown_type $data
	 * 
	 * @return string sign data
	 */
	public function Sign($data){
		try{
			return $this->rsa_help->sign($data, $this->sd_key_coll["privKey"]);
		}catch (Exception $e){
			$this->LastError = "签名数据过程错误";
			error_log(date("[Y-m-d H:i:s]")." -[ error : " .$this->LastError. "\n", 3, "/tmp/sd_plug_err.log");
		}
	}
	
	/**
	 * 
	 * @param unknown_type $data
	 * @param unknown_type $signtype
	 * @return Ambigous <string, Ambigous, unknown, 签名后的数据密文数据, 加密后的数据, NULL>
	 */
	public function signature($data,$signtype){
		$res_sign = '';
		try{
			if($signtype === SD_SIGNTYPE_MD5HEX){
				$res_sign = HashUtil::sign($data, SD_ALG_MD5);
			}else if($signtype === SD_SIGNTYPE_SHA1HEX){
				$res_sign = HashUtil::sign($data, SD_ALG_SHA1);
			}else{
				$res_sign = $this->rsa_help->sign($data, $this->sd_key_coll["privKey"]);
			}
		}catch (Exception $e){
			$this->LastError = "签名数据过程错误";
			error_log(date("[Y-m-d H:i:s]")." -[ error : " .$this->LastError. "\n", 3, "/tmp/sd_plug_err.log");
		}
		return $res_sign;
	}
	
	/**
	 * Check RSA sign data
	 * 
	 * @param unknown_type $data  org data
	 * @param unknown_type $sign
	 * 
	 * @return boolean  verify result
	 */
	public function VerifySign($data, $sign){
		try{
			return $this->rsa_help->verify($data, $this->sd_key_coll["pubKey"], $sign);
		}catch (Exception $e){
			$this->LastError = "验证签名错误";
			error_log(date("[Y-m-d H:i:s]")." -[ error : " .$this->LastError. "\n", 3, "/tmp/sd_plug_err.log");
		}
	}
	
	/**
	 * 
	 * @param unknown_type $data
	 * @param unknown_type $signtype
	 * @param unknown_type $sign
	 * 
	 */
	public function verify($data,$signtype, $sign){
		$res_verify = false;
		try{
			if($signtype === SD_SIGNTYPE_MD5HEX){
				$res_verify = HashUtil::verify($data, $sign, SD_ALG_MD5);
			}else if($signtype === SD_SIGNTYPE_SHA1HEX){
				$res_verify = HashUtil::verify($data, $sign, SD_ALG_SHA1);
			}else{
				$res_verify =  $this->rsa_help->verify($data, $this->sd_key_coll["pubKey"], $sign);
			}
		}catch (Exception $e){
			$this->LastError = "验证签名错误";
			error_log(date("[Y-m-d H:i:s]")." -[ error : " .$this->LastError. "\n", 3, "/tmp/sd_plug_err.log");
		}
		return $res_verify;
	}
	
	/**
	 * 
	 * @param unknown_type $plainText 明文
	 * @param unknown_type $key 3des密钥 十六进制编码
	 * @return 3des加密后的数据
	 */
	public function enc3des($plainText,$key){
		$cipherText = '';
		try{
			$realkey = HexUtil::hex2bin($key);
			$cipherText = $this->triple_tools->encrypt($plainText, $realkey);
		}catch(Exception $e){
			$this->LastError = "3des加密错误";
			error_log(date("[Y-m-d H:i:s]")." -[ error : " .$this->LastError. "\n", 3, "/tmp/sd_plug_err.log");
		}
		return $cipherText;
	}
	
	/**
	 * 
	 * @param unknown_type $cipherText 密文
	 * @param unknown_type $key 3des密钥 十六进制编码
	 * @return 3des解密后的数据
	 */
	public function decr3des($cipherText,$key){
		$plainText = '';
		try{
			$realkey = HexUtil::hex2bin($key);
			$plainText = $this->triple_tools->decrypt($cipherText, $realkey);
		}catch(Exception $e){
			$this->LastError = "3des解密错误";
			error_log(date("[Y-m-d H:i:s]")." -[ error : " .$this->LastError. "\n", 3, "/tmp/sd_plug_err.log");
		}
		return $plainText;
	}
}

/*
header ( "Content-Type:text/html;charset=UTF-8" );
//插件模式
$model = SD_USE_MODEL;

$sand_plug = new SandPayUtil($model);

$sandPublicPath = "PBK_SAND_20121011141230.cer"; //"D:/Apache Software Foundation/Apache2.2/htdocs/SandPayPHP/key/product/PBK_888002188880010_20121113185620.cer";//"D:/Apache Software Foundation/Apache2.2/htdocs/SandPayPHP/key/product/PBK_SAND_20110322000410.cer";//"D:/Apache Software Foundation/Apache2.2/htdocs/SandPayPHP/key/PBK_SAND_20121011141230.cer"; //"D:/document/sand/sandpaykey/publicKey.cer";
 
$merPrivatePath = "PK_48072900_20141027172611.cer" ; // "D:/Apache Software Foundation/Apache2.2/htdocs/SandPayPHP/key/product/PK_888002188880010_20121113185620.cer";//"D:/Apache Software Foundation/Apache2.2/htdocs/SandPayPHP/key/PK_888002148160001_20121011141230.cer"; //D:/document/sand/sandpaykey/privateKey";

$merId = "666001041310001"; //"888002148160001";

$load_result = $sand_plug->LoadKeyFile($merId, $merPrivatePath, $sandPublicPath);

echo "加载密钥的结果：" .$load_result. "<br>";

$gen_des_key = $sand_plug->GenWorkKey();

echo "生成工作密钥的结果：" .$gen_des_key. "<br>";

echo "生成工作密钥:" . $sand_plug->GetKey("desKey"). "<br>";

//$desKey = "kBgT9JfZpcNlHHeq8sqbhcG5";

//$sand_plug->SetWorkkey($desKey);

$work_key = $sand_plug->EncryptWorkKey();

echo "加密工作密钥:" . $work_key. "<br>";

$sand_plug->DecryptWorkKey($work_key);

echo "解密后的工作密钥:" . $sand_plug->GetKey("desKey"). "<br>";

$key = '313233343536373830393837363534333132333435363738';
$plainText = '测试123';

$cipherText = $sand_plug->enc3des($plainText, $key);

echo "3des 加密后结果：" . $cipherText . "<br>";

echo "3des 解密后数据：" . $sand_plug->decr3des($cipherText, $key)."<br>";

$plainText = "version=01&charset=UTF-8&trans_type=0001&merchant_id=666001041310001&merchant_name=fissleracademy&goods_content=&custome_ip=&order_id=test20141028150957258&sm_billno=&order_time=20141028150957&order_amount=000000000001&currency=156&pay_kind=&front_url=http://test.fissleracademy.com.cn:8080/temp/b.php&back_url=http://test.fissleracademy.com.cn:8080/temp/c.php&merchant_attach=";

$enr = $sand_plug->EncryptData($plainText);

echo "加密后数据:" . $enr. "<br>";

$der = $sand_plug->DecryptData($enr);

echo "解密后数据:" . $der. "<br>";

$sign = $sand_plug->Sign($plainText);

echo "签名后数据:" . $sign. "<br>";

//$sign = "251CD7537C45DA2114D11975C093A67C5E6AD3295F934411F77C3990757927EE44E2F3129ED9EE011810D72E1652CE6A16CB32826C33A477713F19F05F3B1E2691618469F41D8F712D541E07349DD0358C8DA020506D4EE0DA740C9D632C26A53137C8FE5BF914F6654DD30AC6C4D3951BFBF92403998FF476E5A9CC4E9F66A3";

$verify = $sand_plug->VerifySign($plainText, $sign);

echo "签名验证的结果:<br>";
var_dump($verify);

$res_sign = $sand_plug->signature($plainText, SD_SIGNTYPE_MD5RSA);
//$res_sign = "251CD7537C45DA2114D11975C093A67C5E6AD3295F934411F77C3990757927EE44E2F3129ED9EE011810D72E1652CE6A16CB32826C33A477713F19F05F3B1E2691618469F41D8F712D541E07349DD0358C8DA020506D4EE0DA740C9D632C26A53137C8FE5BF914F6654DD30AC6C4D3951BFBF92403998FF476E5A9CC4E9F66A3";
echo "signature-->" . $res_sign. "<br>";

$res_verify = $sand_plug->verify($plainText, SD_SIGNTYPE_MD5RSA, $res_sign);

echo "res_verify--><br>";
var_dump($verify);
*/
?>