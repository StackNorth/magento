<?php
/****
 *
 * Class D1m_GeeTest_CaptchaController
 */
class D1m_GeeTest_CaptchaController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        /* @var $GtSdk D1m_GeeTest_Model_Util*/
        $GtSdk = Mage::getModel('d1m_geeTest/util');

      //  session_start();
        $user_id = "";
        $status = $GtSdk->pre_process($user_id);
        $_SESSION['gtserver'] = $status;
        $_SESSION['user_id'] = $user_id;
        echo $GtSdk->get_response_str();
    }
}
