<?php
/**
 *  XML 相关
 *
 * Class D1m_Common_Helper_Xml
 */
class D1m_Common_Helper_Xml extends Mage_Core_Helper_Abstract
{
    /**
     *   convert xml to Array
     *
     * @param $xml
     * @return SimpleXMLElement
     */
    public function convertToArray($xml)
    {
        try
        {
            $xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
            return json_decode(json_encode($xml),true);
        }catch (Exception $e)
        {
            Mage::logException($e);
        }

        return ;
    }
}