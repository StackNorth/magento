<?php
/**
 * Base64 字符转化工具
 *
 * @author Nano
 *
 * @since 2012-04-23 15:14:14
 */
class  Base64 {
	
	public static function encode($data){
		return base64_encode($data);
	}
	
	public static function decode($data){
		return base64_decode($data);
	}
	
}

?>