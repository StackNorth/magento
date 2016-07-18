<?php
class D1m_Credits_Adminhtml_CreditsorderController extends Mage_Adminhtml_Controller_Action
{
   
    
    public function indexAction()
    {                
        $this->loadLayout();

        $this->_setActiveMenu('etam/d1m_credits_order');
        $this->_addBreadcrumb(Mage::helper('d1m_credits')->__('Manage'), Mage::helper('d1m_credits')->__('Manage Credit Order'));
        $this->_addContent($this->getLayout()->createBlock('d1m_credits/adminhtml_creditorder_list'));

        $this->renderLayout();
    }
    


	public function exportCsvAction()
    {
        $fileName   = 'creditorders.csv';
        $content    = $this->getLayout()->createBlock('d1m_credits/adminhtml_creditorder_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'creditorders.xml';
        $content    = $this->getLayout()->createBlock('d1m_credits/adminhtml_creditorder_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $this->loadLayout();

        $recordId  = (int) $this->getRequest()->getParam('id');
        $recordModel   = Mage::getModel('d1m_credits/order');
        $record = $recordModel->load($recordId);

        Mage::register('credit_order', $record);
        Mage::register('current_credit_order', $record);

        $this->_setActiveMenu('etam/d1m_credits_order');
        $this->_addBreadcrumb(Mage::helper('d1m_credits')->__('Manage Credit order'), Mage::helper('d1m_credits')->__('Manage Credit Order'));

        $this->_addContent($this->getLayout()->createBlock('d1m_credits/adminhtml_creditorder_edit'));
        $this->renderLayout();
    }



    public function saveAction()
    {

        if ($this->getRequest()->getPost())
        {

            $credits = Mage::getModel('d1m_credits/credits');
            $creditsId = $this->getRequest()->getParam('id', false);
            if ($creditsId) {
	            $credits->load($creditsId);
	        }

        	$postData = $this->_filterPostData($this->getRequest()->getPost());

	        if (!isset($postData['record'])) {
	            $this->_getSession()->addError(
	                Mage::helper('d1m_credits')->__('Error while saving this credits. Please try again later.')
	            );
	            $this->_redirect('*/*/edit', array('_current'=>true));
	            return;
	        }

	        $data = new Varien_Object($postData['record']);

	        //check brand id
	        if($credits->checkCustomerIdExists($data->getCustomerId(), $creditsId))
	        {
	        	$this->_getSession()->addError(
    					Mage::helper('d1m_credits')->__('Error while saving this credit. the credit for this customer is already exists.')
    			);
    			$this->_getSession()->setRecordData($data->getData());
    			$this->_redirect('*/*/edit', array('_current'=>true));
    			return;
	        }


	        $credits
		        	->setCustomerId($data->getCustomerId())
		        	->setCreditAmount($data->getCreditAmount())
		            ;

	        try
	        {
                //$credits->historyOrderNo =  '1111';
                $credits->historyDesc =  'change it from the backend directly.';

	            $credits->save();

	            $this->_getSession()->addSuccess(
	                Mage::helper('d1m_credits')->__('Credits was successfully saved.')
	            );
	            if ($this->getRequest()->getParam('_continue')) {
	                $this->_redirect('*/*/edit', array('_current'=>true, 'id'=>$credits->getId()));
	            } else {
	                $this->_redirect('*/*/');
	            }
	        } catch (Exception $e) {
	            $this->_getSession()->addError($e->getMessage());
	            $this->_getSession()->setRecordData($credits->getData());
	            $this->_redirect('*/*/edit', array('_current'=>true));
	        }

        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {

        Mage::getSingleton('adminhtml/session')->addError( Mage::helper('d1m_credits')->__('Credits can not be deleted.'));
        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));

        $this->_redirect('*/*/');
    }


    // mass action
    public function massDeleteAction()
    {
        Mage::getSingleton('adminhtml/session')->addError( Mage::helper('d1m_credits')->__('Credits can not be deleted.'));
        $this->_redirect('*/*/index');
    }

    /**
     * Filtering posted data. Converting localized data if needed
     *
     * @param array
     * @return array
     */
    protected function _filterPostData($data)
    {
        if(isset($data['record'])) {
            $_data = $data['record'];
            //$_data = $this->_filterDateTime($_data, array('start_time', 'end_time'));
            $data['record'] = $_data;
        }
        return $data;
    }
    
}    
