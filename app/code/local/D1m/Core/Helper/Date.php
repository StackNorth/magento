<?php
class D1m_Core_Helper_Date extends Mage_Core_Helper_String
{

    /**
     *  格式化时间,返回ISO 8601 date所通用的时间格式
     * @param $timestamp
     * @param string $timezone
     */
    public function formatXsdDate($timestamp=NULL,$timezone='+08:00'){
            return  Mage::app()->getLocale()->date($timestamp)->toString('yyyy-MM-ddTHH:mm:ss').$timezone;
    }

    public function formatDatetime($format='yyyy-MM-dd',$timestamp=NULL){
        return  Mage::app()->getLocale()->date($timestamp)->toString($format);
    }

    public function formatErpDatetime($format='yyyy-MM-dd HH:mm:ss',$timestamp=NULL){
        if (empty($format)){
            $format='yyyy-MM-dd HH:mm:ss';
        }
        return  Mage::app()->getLocale()->date($timestamp)->toString($format).'.000';
    }
}
