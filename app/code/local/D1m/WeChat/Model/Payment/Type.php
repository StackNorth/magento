<?php
/**
 *
 * 微信支付的类型
 *
 * Class D1m_WeChat_Model_Payment_Type
 */
class D1m_WeChat_Model_Payment_Type extends Varien_Object
{
    /**
     *  使用 网页支付 JS API（网页内）支付
     */
    const TRADE_TYPE_JSAPI    = 'JSAPI';

    /***
     *  使用 native 的扫码支付
     */
    const TRADE_TYPE_NATIVE   =  'NATIVE';

    /***
     *  获得支付的类型
     *
     * @param $typeCode
     * @return array
     */
    static  public  function getType($typeCode)
    {
        $types = array(
            self::TRADE_TYPE_JSAPI => '微信网页支付(JS API)',
            self::TRADE_TYPE_NATIVE=>'微信扫码支付(NATIVE)',
        );

        if (array_key_exists($typeCode,$types))
        {
            return $types[$typeCode];
        }

        return $types;
    }

}