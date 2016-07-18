<?php
class D1m_UnionQuick_Block_Redirect extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
        /** @var D1m_UnionQuick_Model_Payment $standard */
        $standard = Mage::getModel('d1m_unionquick/payment');

        $form = new Varien_Data_Form();
        $form->setAction($standard->getUnionQuickURL())
            ->setId('unionquick_checkout')
            ->setName('unionquick_checkout')
            ->setMethod('post')
            ->setUseContainer(TRUE);

        foreach($standard->getStandardCheckoutFormFields() as $k => $v)
        {
            $form->addField($k, 'hidden',
                array(
                    'name' => $k,
                    'value' => $v
                ));
        }

        $html = '<html><body>';
        $html .= $this->__('正在转向银联支付');
        $html .= $form->toHtml();
        $html .= '<script type="text/javascript">setTimeout(function(){document.getElementById("unionquick_checkout").submit();}, 1000);</script>';
        $html .= '</body></html>';
        return $html;
    }
}