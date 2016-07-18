<?php
class D1m_Customapi_Block_Form extends Mage_Core_Block_Template
{
	
	public function __construct()
    {
    	
    	$templateFile = 'customapi/form.phtml';
        $this->setTemplate($templateFile);
    	
        parent::__construct();
    }
	
	
	
}