<?php
/**
 * RSAHelp 非对称算法RSA工具
 *
 * @author Nano
 */
interface RSAHelp {

	/**
	 * 公钥加密
	 * 
	 * @param unknown_type $plaintext 明文
	 * @param unknown_type $pubkey_pem 公钥
	 * 
	 * @return 加密后的数据
	 * 
	 */
	public   function  encryptByPublic($plaintext,$pubkey_pem);
	
	/**
	 * 私钥加密
	 * @param unknown_type $plaintext  文明
	 * @param unknown_type $privkey_pem 私钥
	 * 
	 * @return 加密后的数据
	 */
	public   function  encryptByPrivate($plaintext,$privkey_pem);
	
	
	/**
	 * 私钥解密
	 * @param unknown_type $crypted  密文
	 * @param unknown_type $privkey_pem 私钥
	 * 
	 * @return 解密后的数据
	 */
	public   function  decryptByPrivate($crypted,$privkey_pem);
	
	
	/**
	 * 公钥解密
	 * @param unknown_type $crypted  密文
	 * @param unknown_type $pubkey_pem 公钥
	 * 
	 * @return 解密后的数据
	 */
	public   function  decryptByPublic($crypted,$pubkey_pem);
	
	
	/**
	 * 签名
	 * 
	 * @param unknown_type $plaintext 明文
	 * @param unknown_type $privkey_pem 私钥
	 * 
	 * @return 签名后的数据密文数据
	 */
	public   function  sign($plaintext,$privkey_pem);
	
	
	/**
	 * 验签
	 * 
	 * @param unknown_type $plaintext 原始明文
	 * @param unknown_type $pubkey_pem 公钥
	 * @param unknown_type $sign 签名后的数据
	 * 
	 * @return true/false 返回签名验证是否通过
	 */
	public function verify($plaintext,$pubkey_pem,$sign);
	
	/**
	 * 
	 * @param unknown_type $certPath 证书路径
	 * @param unknown_type $certPwd  证书密码
	 * 
	 * @return 获得私钥
	 */
	public function loadPk12Cert($certPath,$certPwd);
	
	/**
	 * @param unknown_type $certPath 证书路径
	 *
	 * @return 获得公钥
	 */
	public function loadX509Cert($certPath);
	
}

?>