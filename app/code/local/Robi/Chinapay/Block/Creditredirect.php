<?php

class Robi_Chinapay_Block_Creditredirect extends Mage_Core_Block_Abstract
{

	protected function _toHtml()
	{
		$standard = Mage::getModel('chinapay/creditspayment');
        $form = new Varien_Data_Form();
        
        $form->setAction($standard->getGatewayUrl())
            ->setId('chinapay_creditpayment_checkout')
            ->setName('chinapay_creditpayment_checkout')
            ->setMethod('POST')
            ->setUseContainer(true);
            
        foreach ($standard->setPayment($this->getPayment())->getStandardCheckoutFormFields() as $field => $value) {
            $form->addField($field, 'hidden', array('name' => $field, 'value' => $value));
        }

        $formHTML = $form->toHtml();

        $html = '<html><body>';
        $html.= $this->__('You will be redirected to Chinapay in a few seconds.');
        $html.= $formHTML;
        $html.= '<script type="text/javascript">document.getElementById("chinapay_creditpayment_checkout").submit();</script>';
        $html.= '</body></html>';

        return $html;
    }
}