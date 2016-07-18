<?php
class D1m_WeChat_WeixinController extends Mage_Core_Controller_Front_Action
{
    /**
     *  validate
     *
     * @return bool
     * @throws Exception
     * @throws Mage_Core_Exception
     */
    private function _checkSignature()
    {
        try{
            $token = D1m_WeChat_Model_Config::getToken();
        }catch (Mage_Core_Exception $e)
        {
            throw $e;
        }

        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function indexAction()
    {
        if (isset($_GET['echostr']))
        {
            $echoStr = $_GET["echostr"];

            //valid signature , option
            if($this->_checkSignature())
            {
                echo $echoStr;
                exit;
            }
            $this->valid();
        }
        else
        {
            //get post data, May be due to the different environments
            $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
            if (empty($postStr))
            {
                echo "";
                exit;
            }
            //libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        }
    }

    /***
     *  微信，生成二维码
     *
     */
    public function qrCodeAction()
    {
        require_once 'lib/phpqrcode/phpqrcode.php';
        $url = urldecode($_GET["data"]);
       // QRcode::png($url);
        QRcode::png($url, false, QR_ECLEVEL_L, 9, 4);
    }

    /***
     *  获得网站的 open id
     *
     *  @return
     */
    public function getOpenIdAction()
    {
        $openId = $this->getRequest()->getParam('openId');

        echo $openId;
        exit();
    }
}