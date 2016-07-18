<?php
/**
 *
 * Class D1m_Common_Helper_String
 */
class D1m_Common_Helper_String extends Mage_Core_Helper_Abstract
{
    /**
     *  define format charset
     */
    const CHARSET_FORMAT_ALPHANUM = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    const CHARSET_FORMAT_ALPHA     = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const CHARSET_FORMAT_NUMBER   = '0123456789';

    /***
     *  get format charset
     *
     * @param null $format
     * @return array
     */
    private function _getFormatCharset($format=null)
    {
        $formatCharsets = array(
            'alphanum' => self::CHARSET_FORMAT_ALPHANUM,
            'alpha'    =>  self::CHARSET_FORMAT_ALPHA,
            'num'      =>  self::CHARSET_FORMAT_NUMBER
        );

        if(!is_null($format))
        {
            return $formatCharsets[$format];
        }

        return $formatCharsets;
    }

    /**
     * convert  unknown  character
     * @param $str
     * @return mixed|string
     */
    public function vnFilter($str)
    {
        if (!is_string($str) || !strlen(trim($str)))
        {
            return ;
        }

        $unicode = array(
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
            'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'D' => 'Đ',
            'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'I' => 'Í|Ì|Ỉ|Ĩ|Ị',
            'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        );

        foreach ($unicode as $nonUnicode => $uni) {

            $str = preg_replace("/($uni)/i", $nonUnicode, $str);
        }
        $urlKey = preg_replace('#[^0-9a-z]+#i', '', Mage::helper('catalog/product_url')->format($str));
        $urlKey = strtolower($urlKey);
        $urlKey = trim($urlKey, '-');
        return $urlKey;
    }

    /**
     * Generate coupon code
     *
     * @return string
     */
    public function generateRandom($format='num',$split=1,$length = 6,$splitChar='')
    {
        $length  = max(1, (int) $length);
        $split   = max(0, (int)$split);

        $charset =  str_split((string)$this->_getFormatCharset($format));

        $code = '';
        $charsetSize = count($charset);
        for ($i=0; $i<$length; $i++) {
            $char = $charset[mt_rand(0, $charsetSize - 1)];
            if ($split > 0 && ($i % $split) == 0 && $i != 0) {
                $char = $splitChar . $char;
            }
            $code .= $char;
        }

        return $code;
    }
}
