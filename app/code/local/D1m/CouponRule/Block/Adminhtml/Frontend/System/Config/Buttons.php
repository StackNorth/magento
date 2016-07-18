<?php
class D1m_CouponRule_Block_Adminhtml_Frontend_System_Config_Buttons extends Mage_Adminhtml_Block_System_Config_Form_Field
{
 /*
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);

        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button');

        $params = array(
            'website' => $buttonBlock->getRequest()->getParam('website')
        );

        $data = array(
            'label'     => Mage::helper('couponRule')->__('Send Test Sms'),
            'onclick'   => 'if (confirm(\''.Mage::helper('couponRule')->__('Is All Configuration is Saved ?').'\')) { setLocation(\''.$this->getUrl("couponRule/send/testsendemail", $params) . '\' ) }',
            'class'     => '',
        );

        $html = $buttonBlock->setData($data)->toHtml();

        return $html;
    }
*/

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $this->setElement($element);
        $fromStore = $element->getScopeId();
        $layout = $this->getLayout();
        $flushTag = $element->getOriginalData('flush_tag');
     //   $flushUrl = Mage::getUrl('couponRule/send/testsendemail');
        $button = $layout->createBlock('adminhtml/widget_button');
        $button->setType('button')
            ->setClass('scalable')
            ->setLabel('test send message')
            ->setOnClick('javascript:sendsmsmessage();');

        $params = array(
            'website' => $button->getRequest()->getParam('website')
        );
        $message_test_url = $this->getUrl("couponRule/send/sendsmsmessage", $params);
//Mage::helper('logger/data')->info($flushUrl);

        $buttonHTML = $button->toHtml();
        $jsFunction = '
        <script type="text/javascript">
        function sendsmsmessage'.$flushTag.'()
        {
            if(confirm("Are you sure to send message?")) {
                new Ajax.Request("'.$message_test_url.'",{
                    method: "get",
                    onSuccess: function(transport){
                        if (transport.responseText=="1"){
                            alert("send success!.");
                        }
                    },
                    onFailure: function (transport){
                        alert("error, the error information.you can see the log!");
                    }
                });
            }
        }
        </script>';
        $html = $layout->createBlock('core/text','flush-button')->setText($jsFunction.$buttonHTML)->toHtml();
        return $html;
    }
}
?>