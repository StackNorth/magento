<?php

class D1m_Sandpay_Block_Redirect extends Mage_Core_Block_Abstract
{

	protected function _toHtml()
	{
        /* @var $standard D1m_Sandpay_Model_Payment */
		$standard = Mage::getModel('sandpay/payment');
        $form = new Varien_Data_Form();
        
        $form->setAction($standard->getGatewayUrl())
            ->setId('sandpay_payment_checkout')
            ->setName('sandpay_payment_checkout')
            ->setMethod('POST')
            ->setUseContainer(true);
            
        foreach ($standard->setPayment($this->getPayment())->getStandardCheckoutFormFields() as $field => $value) {
            $form->addField($field, 'hidden', array('name' => $field, 'value' => $value));
        }

        $formHTML = $form->toHtml();

        $html = '<html><body>';
        $html.= $this->__('You will be redirected to Sandpay in a few seconds.');
        $html.= $formHTML;
        $html.= '<script type="text/javascript">document.getElementById("sandpay_payment_checkout").submit();</script>';

/*        $html.='
<script src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.8.0.js"></script>
<script>
    $(document).ready(function(){
        $("button").click(function()
        {
            $("#sandpay_payment_checkout").submit();
        });
    });
</script>
<button>提交表单</button>';
  */
        $html.= '</body></html>';

        return $html;
    }
}