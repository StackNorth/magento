<?php
class D1m_Customapi_Block_Test extends Mage_Core_Block_Template
{
	
	public function __construct()
    {
    	
    	$templateFile = 'customapi/test.phtml';
        $this->setTemplate($templateFile);
    	
        parent::__construct();
    }
	
	
	
}