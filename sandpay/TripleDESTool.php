<?php
include_once("HashUtil.php");
/**
 * 3DES 对称算法工具
 *
 * @author Nano
 */
class TripleDESTool {
	
	private $init_model = array(
			'0','1','2','3','4','5','6','7','8','9',
			'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
			'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
	
	public function genTripleDES(){
		$key = "012345677654321001234567";
		$temp = "";
		for ($i = 0; $i < 24;$i++){
			$index = rand(0, 61);
			$temp .= $this->init_model[$index];
		}
		$key = $temp;
		return $key;
	}
	
	/**
	 * 3DES 加密工具
	 * 
	 * @param unknown_type $str
	 * @param unknown_type $key
	 */
	public function encrypt($str,$key)
	{
		$size = mcrypt_get_block_size(MCRYPT_3DES, MCRYPT_MODE_ECB);
		
		$str = $this->fillNoPadding($str,$size);
		
		$data =  mcrypt_encrypt(MCRYPT_3DES, $key, $str, MCRYPT_MODE_ECB);
	
		return strtoupper(HexUtil::bin2hex($data));
	}
	
     function fillNoPadding($str,$block)
     {
		$len = strlen($str);
		$strlen = "".$len;
		$lenlen = strlen($strlen);
		$str = str_repeat('0', 4-$lenlen).$strlen.$str;
		$len = strlen($str);
		$input = 0x00;
		if(($pad = $block - $len%$block) < $block){
			$str .= str_repeat(chr($input), $pad);
		}
		return $str;
	} 
	
	function unfillNoPadding($str,$block){
//不用去后面的空格		
// 		$repad = chr(0x00);
// 		$pad = chr($str[($len = strlen($str)) - 1] );
// 		if($repad === $pad){
// 			$str = rtrim($str,$repad);
// 		}
		$len = substr($str,0,4);
		return	substr($str, 4,$len);
	}
	
	
	/**
	 * 3DES 解密工具
	 * @param unknown_type $str
	 * @param unknown_type $key
	 */
	public function decrypt($str,$key)
	{
 		$binstr = HexUtil::hex2bin($str);
 		
 		$decrypt_data = mcrypt_decrypt(MCRYPT_3DES, $key, $binstr, MCRYPT_MODE_ECB);
 		
 		$size = mcrypt_get_block_size(MCRYPT_3DES, MCRYPT_MODE_ECB);
 		
 		$decrypt_data = $this->unfillNoPadding($decrypt_data,$size);
 		
 		return $decrypt_data;
 	}
}

?>