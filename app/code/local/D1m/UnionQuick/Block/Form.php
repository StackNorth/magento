<?php
class D1m_UnionQuick_Block_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('unionquick/form.phtml');
        parent::_construct();
    }
}