<?php

class Robi_Msgnotice_FailedactionController extends Mage_Core_Controller_Front_Action
{

    public function preDispatch()
    {
        parent::preDispatch();

        return $this;
    }

   

	public function resendfailedactionAction()
	{
		
		header('Content-type:text/html; charset=utf-8');
        $this->getResponse()->setHeader('Content-type','text/html; charset=utf-8');
		
		$id = $this->getRequest()->getParam('id',false);
		
		if($id)
		{
			
			$failedaction = Mage::getModel('msgnotice/failedaction')->load($id);
			if($failedaction && $failedaction->getId() && $failedaction->getStatus() == 0 )
			{
				$status = Mage::helper('msgnotice')->resendFailedAction($failedaction);
				
				if($status)
					echo '发送成功';
				else
					echo '失败';
			}
			else
			{
				echo '没有找到对应的失败记录。';
			}
		}
		else
		{
			echo '缺少ID。';
		}
		
		die();
	
		
	}

   

}

