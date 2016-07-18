<?php
/**
 * 添加验证码
 *
 * Class D1m_GeeTest_Model_Captcha_Observer
 */
class D1m_GeeTest_Model_Captcha_Observer extends Mage_Captcha_Model_Observer
{

    /****
     *  检查是否可以进行短信发送
     *
     * @param Varien_Event_Observer $observer
     * @return $this|Mage_Captcha_Model_Observer
     */
    public function checkSmsCanSend($observer)
    {
        $controller = $observer->getControllerAction();

        /* @var $helper D1m_GeeTest_Helper_Data */
        $helper  = Mage::helper('d1m_geeTest');
        $result = false;
        $returnResult = array();

        if ($helper->isRequired(D1m_GeeTest_Model_Config::CAPTCHA_FRONTEND_AREAS_FORM_SEND_SMS))
        {
            $gtUtil =  new D1m_GeeTest_Model_Util();
            $gtChallenge = $controller->getRequest()->getPost('geetest_challenge');
            $gtValidate = $controller->getRequest()->getPost('geetest_validate');
            $gtt= $controller->getRequest()->getPost('geetest_seccode');

            $result = $gtUtil->sucess_validate($gtChallenge, $gtValidate, $gtt,$_SESSION['user_id']);
            $prt=array('$gtUtil'=>$gtUtil,'$gtChallenge'=>$gtChallenge,'$gtValidate'=>$gtValidate,'$gtt'=>$gtt,'$result'=>$result);
          //  print_r($prt);
            if (!$result)
            {
                $controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);

                if (Mage::app()->getRequest()->isAjax())
                {
                    $returnResult = array(
                        'status'=>false,
                        'msg'=>Mage::helper('d1m_geeTest')->__('请拖动滑块完成验证!'),
                        'redirect_url'=> Mage::helper('customer')->getLoginUrl()
                    );
                    $controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($returnResult));
                }else{
                    Mage::getSingleton('customer/session')->addError(Mage::helper('d1m_geeTest')->__('Incorrect CAPTCHA.Please Make it Correctly!'));
                    $url =  Mage::helper('customer')->getLoginUrl();
                    $controller->getResponse()->setRedirect($url);
                }
            }
        }

        return $this;
    }

    /****
     *
     * @param Varien_Event_Observer $observer
     * @return $this|Mage_Captcha_Model_Observer
     */
    public function checkForgotPassword($observer)
    {
           return $this;
    }

    /***
     *
     * @param Varien_Event_Observer $observer
     * @return $this|Mage_Captcha_Model_Observer
     */
    public function checkUserCreate($observer)
    {
            return $this;
    }
}
