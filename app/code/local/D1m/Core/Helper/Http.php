<?php
class D1m_Core_Helper_Http extends Mage_Core_Helper_Abstract
{
    /**
     * 检查URL是否可以访问
     * @param $http_link
     * @return mixed
     */
    public function getHttpCode($http_link, $ssl=false)
    {
        $ch = curl_init($http_link);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $ssl);

        $result = curl_exec($ch);

        if(!$result){
            $curl_error = curl_error($ch);
            $curl_errno = curl_errno($ch);
            Mage::log('The Http Link error, the error string is  '.$curl_error,null,D1m_Core_Helper_Log::SOA_ARVATO_CRM_SOAP_LOG_PATH);
            Mage::log('The Http Link error, the error id is  '.$curl_errno,null,D1m_Core_Helper_Log::SOA_ARVATO_CRM_SOAP_LOG_PATH);
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code != D1m_Vip_Model_VipSource::VIP_GRASP_LOG_HTTP_STATUS_SUCCESS_CODE){
            Mage::log('HTTP CODE ERROR, the error id is  ',null,D1m_Core_Helper_Log::SOA_ARVATO_CRM_SOAP_LOG_PATH);
        }

        curl_close($ch);

        return $http_code;
    }
}
