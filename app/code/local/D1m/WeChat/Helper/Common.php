<?php
/**
 * 微信的公用调用类
 *
 * Class D1m_WeChat_Helper_Common
 */
class D1m_WeChat_Helper_Common extends Mage_Core_Helper_Abstract
{
    /**
     *  http 请求的默认超时时间
     */
    const HTTP_REQUEST_TIMEOUT  =   30;

    /**
     *  统一使用 CURL来做HTTP的请求处理类
     *  POST方式处理
     * @param $url
     * @param $data
     * @return array
     */
    public function post($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, self::HTTP_REQUEST_TIMEOUT);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $content = curl_exec($ch);

        return $content;
    }

    /**
     *  统一使用 CURL来做HTTP的请求处理类
     *  GET 方式处理
     *
     * @param $url
     * @return mixed
     */
    public function get($url)
    {
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, self::HTTP_REQUEST_TIMEOUT);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);


        //运行curl，结果以json形式返回
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }
}