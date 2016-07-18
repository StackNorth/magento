<?php
include_once("Config.php");
include_once("HexUtil.php");
include_once("Base64.php");
/**
 * Hash 散列算法工具
 *
 * @author Nano
 * 
 * @since 2012-04-24 12：49：49
 */
class HashUtil {
	
	
	/**
	 * @param string $str
	 * @param boolean $raw_output binary
	 * @param string $alg
	 * @param string $format
	 * 
	 * @return string hash data
	 */
	public static  function hashstr($str,$raw_output = true,$alg = SD_ALG_MD5,$format = SD_FMT_HEX){
		$hash = '';
		if($alg === SD_ALG_MD5 ){
			$hash = md5($str,$raw_output);
		}else if($alg === SD_ALG_SHA1 ){
			$hash = sha1($str,$raw_output);
		}
		
		if($raw_output && $format === SD_FMT_HEX){
			$hash = HexUtil::bin2hex($hash);
		}else if($raw_output && $format === SD_FMT_BASE64){
			$hash = Base64::encode($hash);
		}	
		return $hash;
	}
	
	/**
	 * 
	 * @param unknown_type $str 
	 * @param unknown_type $alg
	 * @return Ambigous <string, unknown>
	 */
	public static function sign($str,$alg){
		$sign = '';
		if($alg === SD_ALG_MD5){
			$sign = HexUtil::bin2hex(md5($str,true));
		}else if($alg === SD_ALG_SHA1 ){
			$sign = HexUtil::bin2hex(sha1($str,true));
		}
		return $sign;
	}
	
	/**
	 * 
	 * @param unknown_type $data
	 * @param unknown_type $sign
	 * @param unknown_type $alg : SD_ALG_MD5,SD_ALG_SHA1
	 * @return boolean
	 */
	public static function verify($data,$sign,$alg){
		$res = false;
		$verify = HashUtil::hash($data,true,$alg);
		$sign = HexUtil::hex2bin($sign);
		if($sign === $verify){
			$res = true;
		}else{
			$res = false;
		}
		return $res;
	}
	
	
	/**
	 * @param string $str
	 * @param boolean $raw_output binary
	 * @param string $alg
	 * @param string $format
	 *
	 * @return in raw binary format or hex string hash data
	 */
	public static  function hash($str,$raw_output = true,$alg = SD_ALG_MD5){
		$hash = '';
		if($alg === SD_ALG_MD5 ){
			$hash = md5($str,$raw_output);
		}else if($alg === SD_ALG_SHA1 ){
			$hash = sha1($str,$raw_output);
		}
		return $hash;
	}
}

?>