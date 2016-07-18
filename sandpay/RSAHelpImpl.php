<?php

include_once("HashUtil.php");
require_once('RSAHelp.php');
/**
 * @author Nano
 * 
 * RSA 非对称算法辅助工具
 *
 */
class RSAHelpImpl implements  RSAHelp {
	
	/**
	 * 私钥解密
	 * @param unknown_type $crypted  密文
	 * @param unknown_type $privkey_pem 私钥
	 *
	 * @return 解密后的数据
	 */
	public function decryptByPrivate($crypted, $privkey_pem) {
		try {
			$data = HexUtil::hex2bin($crypted);
			$encr_result = openssl_private_decrypt($data, $decrypted, $privkey_pem,OPENSSL_PKCS1_PADDING);
			if($encr_result){
				return $decrypted;	
			}
		} catch (Exception $e) {
			error_log(date("[Y-m-d H:i:s]")." -[ error :".$e."\n", 3, "/tmp/sd_plug_err.log"); 
			throw $e;
		}
		return null;
	}

	/**
	 * 公钥解密
	 * @param unknown_type $crypted  密文
	 * @param unknown_type $pubkey_pem 公钥
	 *
	 * @return 解密后的数据
	 */
	public function decryptByPublic($crypted, $pubkey_pem) {
		try {
			$data = HexUtil::hex2bin($crypted);
			$encr_result = openssl_public_decrypt($data, $decrypted, $pubkey_pem,OPENSSL_PKCS1_PADDING);
			if($encr_result){
				return $decrypted;	
			}
		} catch (Exception $e) {
			error_log(date("[Y-m-d H:i:s]")." -[ error :".$e."\n", 3, "/tmp/sd_plug_err.log");
			throw $e;
		}
		return null;
	}

	
	/**
	 * 私钥加密
	 * @param unknown_type $plaintext  文明
	 * @param unknown_type $privkey_pem 私钥
	 *
	 * @return 加密后的数据
	 */
	public function encryptByPrivate($plaintext, $privkey_pem) {
		try {
			$encr_result = openssl_private_encrypt($plaintext,$crypted,$privkey_pem,OPENSSL_PKCS1_PADDING);
			if($encr_result){
				return HexUtil::bin2hex($crypted);	
			}
		} catch (Exception $e) {
			error_log(date("[Y-m-d H:i:s]")." -[ error :".$e."\n", 3, "/tmp/sd_plug_err.log");
			throw $e;
		}
		return null;
	}

	/**
	 * 公钥加密
	 *
	 * @param unknown_type $plaintext 明文
	 * @param unknown_type $pubkey_pem 公钥
	 *
	 * @return 加密后的数据
	 */
	public function encryptByPublic($plaintext, $pubkey_pem) {
		try {
			$encr_result = openssl_public_encrypt($plaintext,$crypted,$pubkey_pem,OPENSSL_PKCS1_PADDING);
			if($encr_result){
				return HexUtil::bin2hex($crypted);	
			}
		} catch (Exception $e) {
			error_log(date("[Y-m-d H:i:s]")." -[ error :".$e."\n", 3, "/tmp/sd_plug_err.log");
			throw $e;
		}
		return null;
	}
	
	/**
	 * 签名
	 *
	 * @param unknown_type $plaintext 明文
	 * @param unknown_type $privkey_pem 私钥
	 *
	 * @return 签名后的数据密文数据
	 */
	public function sign($plaintext, $privkey_pem) {
		try {
			$md5Hash = HashUtil::hash($plaintext,true);
			
			return $this->encryptByPrivate($md5Hash, $privkey_pem);
		} catch (Exception $e) {
			error_log(date("[Y-m-d H:i:s]")." -[ error :".$e."\n", 3, "/tmp/sd_plug_err.log");
			throw $e;
		}
	}

	/**
	 * 验签
	 * 
	 * @param unknown_type $plaintext 原始明文
	 * @param unknown_type $pubkey_pem 公钥
	 * @param unknown_type $sign 签名后的数据
	 *
	 * @return true/false 返回签名验证是否通过
	 */
	public function verify($plaintext, $pubkey_pem, $sign) {
		try {
			$md5Hash = HashUtil::hash($plaintext,true);
			
			$decrypted = $this->decryptByPublic($sign, $pubkey_pem);
			
			if($md5Hash === $decrypted){
				return true;
			}else{
				return false;
			}
		} catch (Exception $e) {
			error_log(date("[Y-m-d H:i:s]")." -[ error :".$e."\n", 3, "/tmp/sd_plug_err.log");
			throw $e;
		}
		return false;
	}
	
	/**
	 *
	 * @param unknown_type $certPath 证书路径
	 * @param unknown_type $certPwd  证书密码
	 *
	 * @return 证书内容
	 */
	public function loadPk12Cert($certPath, $certPwd) {
		try {
			$cert_file_handle = fopen($certPath, 'r');
			$pcert12 = array();
			$pkcs12 = fread($cert_file_handle, filesize($certPath));
			fclose($cert_file_handle);
			if(openssl_pkcs12_read($pkcs12,$pcert12,$certPwd)){
				//var_dump($pcert12);
				
				return $pcert12['pkey'];
			}else{
				error_log(date("[Y-m-d H:i:s]")." -[ error :".' 加载PKCS12证书失败 '."\n", 3, "/tmp/sd_plug_err.log");
				return null;
			}
		} catch (Exception $e) {
			error_log(date("[Y-m-d H:i:s]")." -[ error :".$e."\n", 3, "/tmp/sd_plug_err.log");
			throw $e;
		}
	}

	/**
	 * @param unknown_type $certPath 证书路径
	 *
	 * @return 获得公钥
	 */
	public function loadX509Cert($certPath) {
		try {
			$cert_file_handle = fopen($certPath, 'r');
			$cert = fread($cert_file_handle, filesize($certPath));
			fclose($cert_file_handle);
			
			$cert = chunk_split(Base64::encode($cert), 64, "\n");
			$cert = "-----BEGIN CERTIFICATE-----\n" . $cert . "-----END CERTIFICATE-----\n";
			
			$pub_res = openssl_pkey_get_public($cert);
			
			$pubkey_array = openssl_pkey_get_details($pub_res);
			//公钥串
			$pub_key = $pubkey_array["key"];
			
			return $pub_key;
		} catch (Exception $e) {
			error_log(date("[Y-m-d H:i:s]")." -[ error :".$e."\n", 3, "/tmp/sd_plug_err.log");
			throw $e;
		}
	}
	
}

?>