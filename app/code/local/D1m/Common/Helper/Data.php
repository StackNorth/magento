<?php
class D1m_Common_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Common method for sending curl request and retrieve response info, support Get/Post 
     */
    public function curl($url, $vars=array(), $options=array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);

        //设置要采集的URL
        if (!empty($vars)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
        }

        //设置Post参数
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //设置其它curl参数,如timeout等
        $options = array_merge(
            array( 
                CURLOPT_TIMEOUT => 10, 
                CURLOPT_PROXY => null,
                CURLOPT_SSL_VERIFYPEER => false,
            ),
            $options
        );
        foreach ($options as $option => $value) {
            if ($option && $value!==null) {
                curl_setopt($ch, $option, $value);
            }
        }

        //用字符串打印出来。
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

}
