<?php

include_once("HashUtil.php");
/**
 * DESTool 对称算法工具
 *
 * @author Nano
 */
class DESTool {
	
	private $init_model = array(
			'0','1','2','3','4','5','6','7','8','9',
			'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
			'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
	
	
	public function genDES(){
		$key = "01234567";
		$temp = "";
		for ($i = 0; $i < 8;$i++){
			$index = rand(0, 61);
			$temp .= $this->init_model[$index];
		}
		$key = $temp;
		return $key;
	}
	
	
		/**
	 * NoPadding
	 *
	 * 3DES 加密
	 *
	 * @param unknown_type $str 待加密的字符
	 * @param unknown_type $key des密钥
	 * @return string base64
	 */
	public function en3desBase64($str,$key)
	{
		$len = strlen($key);
		if($len != 16 && $len != 24){
			throw new Exception("3DES 密钥长度不是16 或 24位长度");
		}
		//encrypt decrypt encrypt
		try {
			$str = $this->encrypt($str, substr($key, 0,8));
			$str = $this->decrypt($str, substr($key,8,8));
			if($len === 24){
				$str = $this->encrypt($str, substr($key,16,8));
			}else{
				$str = $this->encrypt($str, substr($key,0,8));
			}
			$str = Base64::encode($str);
		} catch (Exception $e) {
			error_log(date("[Y-m-d H:i:s]")." -[ error :".$e."\n", 3, "/tmp/sd_plug_err.log");
			throw $e;
		}
		return $str;
	}

	
	/**
	 * NoPadding
	 *
	 * 3DES 解密
	 * @param unknown_type $str 待解密的字符 Base64
	 * @param unknown_type $key des密钥
	 * @return string
	 */
	public function de3desBase64($str,$key)
	{
		$len = strlen($key);
		if($len != 16 && $len != 24){
			throw new Exception("3DES 密钥长度不是16 或 24位长度");
		}
		//decrypt encrypt decrypt
		try {
			$str = Base64::decode($str);
			$str = $this->decrypt($str, substr($key, 0,8));
			$str = $this->encrypt($str, substr($key,8,8));
			if($len === 24){
				$str = $this->decrypt($str, substr($key,16,8));
			}else{
				$str = $this->decrypt($str, substr($key,0,8));
			}
			//去掉填充
			$size = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_ECB);
			$str = $this->unNoPadding($str,$size);
		} catch (Exception $e) {
			error_log(date("[Y-m-d H:i:s]")." -[ error :".$e."\n", 3, "/tmp/sd_plug_err.log");
			throw $e;
		}
		return $str;
	}
	
	
	/**
	 * NoPadding
	 * 
	 * 3DES 加密
	 *
	 * @param unknown_type $str 待加密的字符
	 * @param unknown_type $key des密钥
	 * @return string
	 */
	public function en3des($str,$key)
	{
		$len = strlen($key);
		if($len != 16 && $len != 24){
			throw new Exception("3DES 密钥长度不是16 或 24位长度");
		}
		//encrypt decrypt encrypt
		try {
			$str = $this->encrypt($str, substr($key, 0,8));
			$str = $this->decrypt($str, substr($key,8,8));
			if($len === 24){
				$str = $this->encrypt($str, substr($key,16,8));
			}else{
				$str = $this->encrypt($str, substr($key,0,8));
			}
				
			$str = strtoupper(HexUtil::bin2hex($str));
		} catch (Exception $e) {
			error_log(date("[Y-m-d H:i:s]")." -[ error :".$e."\n", 3, "/tmp/sd_plug_err.log");
			throw $e;
		}
		return $str;
	}
	
	/**
	 * NoPadding
	 *
	 * 3DES 解密
	 * @param unknown_type $str 待解密的字符
	 * @param unknown_type $key des密钥
	 * @return string
	 */
	public function de3des($str,$key)
	{
		$len = strlen($key);
		if($len != 16 && $len != 24){
			throw new Exception("3DES 密钥长度不是16 或 24位长度");
		}
		//decrypt encrypt decrypt 
		try {
			$str = HexUtil::hex2bin($str);
			$str = $this->decrypt($str, substr($key, 0,8));
			$str = $this->encrypt($str, substr($key,8,8));
			if($len === 24){
				$str = $this->decrypt($str, substr($key,16,8));
			}else{
				$str = $this->decrypt($str, substr($key,0,8));
			}
			//去掉填充
			$size = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_ECB);		
			$str = $this->unNoPadding($str,$size);
		} catch (Exception $e) {
			error_log(date("[Y-m-d H:i:s]")." -[ error :".$e."\n", 3, "/tmp/sd_plug_err.log");
			throw $e;
		}
		return $str;
	}
	
	
	private function encrypt($str,$key)
	{
		$size = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_ECB);
			
		$str = $this->noPadding($str,$size);
			
		$data =  mcrypt_encrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);
	
		return $data;
	
	}
	
	private function decrypt($str,$key)
	{	
		$size = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_ECB);
			
		$str = $this->noPadding($str,$size);
		
		$decrypt_data = mcrypt_decrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);
			
		return $decrypt_data;
	}
	
	
	/**
	 * NoPadding
	 * 
	 * 单DES加密
	 * 
	 * @param unknown_type $str 待加密的字符
	 * @param unknown_type $key des密钥
	 * @return string
	 */
	public function endes($str,$key)
	{
		$size = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_ECB);
			
		$str = $this->noPadding($str,$size);
			
		$data =  mcrypt_encrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);
		
		return strtoupper(HexUtil::bin2hex($data));
	}
	
	/**
	 * NoPadding
	 *
	 * 单DES解密
	 * @param unknown_type $str 待解密的字符
	 * @param unknown_type $key des密钥
	 * @return string
	 */
	public function dedes($str,$key)
	{	
 		$binstr = HexUtil::hex2bin($str);
 		
 		$decrypt_data = mcrypt_decrypt(MCRYPT_DES, $key, $binstr, MCRYPT_MODE_ECB);
 		
 		$size = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_ECB);
 		
 		$decrypt_data = $this->unNoPadding($decrypt_data,$size);
 		
 		return $decrypt_data;
 	}
 	
 	
 	private function noPadding($str,$block)
 	{
		$input = 0x00;
		if(($pad = $block - strlen($str)%$block) < $block){
			$str .= str_repeat(chr($input), $pad);
		}
		return $str;
	}
	
	private function unNoPadding($str,$block)
	{
		$repad = chr(0x00);
		$pad = chr($str[($len = strlen($str)) - 1] );
		if($repad === $pad){
			$str = rtrim($str,$repad);
		}
		return $str;
	}
	

 	public function encryptWithPKCS5($str,$key)
 	 {	
 		$iv=null;
 		foreach ($key as $element)
 			$iv.= chr($element);
 		
 		$size = mcrypt_get_block_size (MCRYPT_DES, MCRYPT_MODE_ECB );
 		
 		$str = $this->pkcs5Padding($str,$size);
 		
 		$data =  mcrypt_encrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB, $iv);
 		
 		return strtoupper(HexUtil::bin2hex($data));
 	}
 
	
 	public function decryptWithPKCS5($str,$key) 
 	{	
 		$iv = null;
 		foreach ($key as $element)
 			$iv.= chr($element);
 		
 		$binstr = HexUtil::hex2bin($str);
 		
 		$str = mcrypt_decrypt(MCRYPT_DES, $key, $binstr, MCRYPT_MODE_ECB,$iv);
 		
 		$str = $this->unpkcs5Padding($str);
 		return $str;
 	}
 	
   private function pkcs5Padding($str,$block)
   {
 		$pad = $block - (strlen($str) % $block);
 	
 		return $str . str_repeat (chr($pad), $pad);
   }
 	
 	private function unpkcs5Padding($str)
 	{
 		 $pad = ord ($str{strlen ($str) - 1});  
        if ($pad > strlen ($str))  
            return false;  
        if (strspn ($str, chr($pad), strlen($str) - $pad ) != $pad)  
            return false;  
        return substr ($str, 0, - 1 * $pad);  
 	}
 	
	public function encryptWithPKCS7($str, $key) 
	{
		$block = mcrypt_get_block_size ( MCRYPT_DES, MCRYPT_MODE_ECB );
		
		$str = $this->pkcs7Padding($str, $block);
		
		return strtoupper(HexUtil::bin2hex(mcrypt_encrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB)));
	}
	
	public function decryptWithPKCS7($str, $key)
   {
		$binstr = HexUtil::hex2bin($str);
		
		$str = mcrypt_decrypt(MCRYPT_DES, $key, $binstr, MCRYPT_MODE_ECB);
	
		$block = mcrypt_get_block_size (MCRYPT_DES, MCRYPT_MODE_ECB);
		
		$str = $this->unpkcs7Padding($str, $block);
		
		return $str;
	}

	private function pkcs7Padding($str,$block)
	{
		if (($pad = $block - (strlen ( $str ) % $block)) < $block) {
			$str .= str_repeat (chr($pad), $pad);
		}
		return $str;
	}
	
	private	function unpkcs7Padding($str,$block)
	{
		$pad = ord ( $str [($len = strlen ( $str )) - 1] );
		if ($pad && $pad < $block && preg_match ( '/' . chr ( $pad ) . '{' . $pad . '}$/', $str)) {
			return substr ( $str, 0, strlen ( $str ) - $pad );
		}
		return $str;
	}
}

?>