<?php
/**
 * Hex  字符转化工具
 *
 * @author Nano
 *
 * @since 2012-04-23 15:14:14
 */
class HexUtil {

	public static function bin2hex($binstr){
		if(empty($binstr)){
			return $binstr;//如果为空,直接返回
		}else{
			return bin2hex($binstr);
		}
	}

	public static function hex2bin($hexstr){
		return pack("H*",$hexstr);
	}
}
?>