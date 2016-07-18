<?php
/**
 *  define different message
 *
 * Class D1m_WeChat_Model_Payment_Message
 */
class D1m_WeChat_Model_Payment_Message extends Mage_Core_Model_Abstract
{
    // |---------------------+-----------------------+------------------+--------+-------------------|
    // |  order_status (ERP) | shipping_status (ERP) | pay_status (ERP) |付款方式 | 官网狀态            |
    //  SYSTEMERROR             接口后台错误  Y  Y  Y  Y  Y
    //  INVALID_TRANSACTIONID   无效 transaction_id  Y  Y  Y  Y  Y
    //  PARAM_ERROR             提交参数错误  Y  Y  Y  Y  Y
    //  ORDERPAID               订单已支付  Y    Yf
    //  OUT_TRADE_NO_USED       商户订单号重复  Y
    //  NOAUTH                  商户无权限  Y
    //  NOTENOUGH               余额不足  Y
    //  NOTSUPORTCARD           不支持卡类型  Y
    //  ORDERCLOSED             订单已关闭  Y    Y
    //  BANKERROR               银行系统异常  Y
    //  REFUND_FEE_INVALID      退款金额大亍支付金额        Y
    //  ORDERNOTEXIST           订单不存在    Y  Y  Y
    // |---------------------+-----------------------+------------------+--------+-------------------|

    /**
     *  以下为微信支付付款时的不同类型
     */
    const  ERROR_CODE_PAYERROR = 'PAYERROR';
    const  ERROR_CODE_SYSTEMERROR = 'SYSTEMERROR';
    const  ERROR_CODE_INVALID_TRANSACTIONID = 'INVALID_TRANSACTIONID';
    const  ERROR_CODE_PARAM_ERROR = 'PARAM_ERROR';
    const  ERROR_CODE_ORDERPAID = 'ORDERPAID';
    const  ERROR_CODE_OUT_TRADE_NO_USED = 'OUT_TRADE_NO_USED';
    const  ERROR_CODE_NOAUTH = 'NOAUTH';
    const  ERROR_CODE_NOTENOUGH = 'NOTENOUGH';
    const  ERROR_CODE_NOTSUPORTCARD = 'NOTSUPORTCARD';
    const  ERROR_CODE_ORDERCLOSED = 'ORDERCLOSED';
    const  ERROR_CODE_BANKERROR = 'BANKERROR';
    const  ERROR_CODE_REFUND_FEE_INVALID = 'REFUND_FEE_INVALID';
    const  ERROR_CODE_ORDERNOTEXIST = 'ORDERNOTEXIST';




    /**
     *  get error message
     *
     * @param null $errorCode
     * @return array|null
     */
    static public function getErrorMessage($errorCode = null)
    {
        $errorMessages = array(
            self::ERROR_CODE_PAYERROR => '支付错误',
            self::ERROR_CODE_SYSTEMERROR => '接口后台错误',
            self::ERROR_CODE_INVALID_TRANSACTIONID => '无效 transaction_id',
            self::ERROR_CODE_PARAM_ERROR => '提交参数错误',
            self::ERROR_CODE_ORDERPAID => '订单已支付',
            self::ERROR_CODE_OUT_TRADE_NO_USED => '商户订单号重复',
            self::ERROR_CODE_NOAUTH => '商户无权限',
            self::ERROR_CODE_NOTENOUGH => '余额不足',
            self::ERROR_CODE_NOTSUPORTCARD => '不支持卡类型',
            self::ERROR_CODE_ORDERCLOSED => '订单已关闭',
            self::ERROR_CODE_BANKERROR => '银行系统异常',
            self::ERROR_CODE_REFUND_FEE_INVALID => '退款金额大亍支付金额',
            self::ERROR_CODE_ORDERNOTEXIST => '订单不存在'
        );

        if (!is_null($errorCode)) {
            if (isset($errorMessages[$errorCode])) {
                return $errorMessages[$errorCode];
            }
            return null;
        }

        return $errorMessages;
    }

}