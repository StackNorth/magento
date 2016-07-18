<?php
class D1m_Invitation_Adminhtml_InvitationController extends Mage_Adminhtml_Controller_Action
{
   
    
    public function indexAction()
    {                
        $this->loadLayout();

        $this->_setActiveMenu('etam/d1m_invitation');
        $this->_addBreadcrumb(Mage::helper('d1m_invitation')->__('Manage'), Mage::helper('d1m_invitation')->__('Manage Invitation'));
        $this->_addContent($this->getLayout()->createBlock('d1m_invitation/adminhtml_invitation_list'));

        $this->renderLayout();
    }
    

	
	public function exportCsvAction()
    {
        $fileName   = 'invitation.csv';
        $content    = $this->getLayout()->createBlock('d1m_invitation/adminhtml_invitation_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'invitation.xml';
        $content    = $this->getLayout()->createBlock('d1m_invitation/adminhtml_invitation_grid')
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
        $recordModel   = Mage::getModel('d1m_invitation/invitation');
        $record = $recordModel->load($recordId);

        Mage::register('invitation', $record);
        Mage::register('current_invitation', $record);
        
        $this->_setActiveMenu('etam/d1m_invitation');
        $this->_addBreadcrumb(Mage::helper('d1m_invitation')->__('Manage Invitation'), Mage::helper('d1m_invitation')->__('Manage Invitation'));

        $this->_addContent($this->getLayout()->createBlock('d1m_invitation/adminhtml_invitation_edit'));
        $this->_addLeft($this->getLayout()->createBlock('d1m_invitation/adminhtml_invitation_edit_tabs'));
        $this->renderLayout();
    }

    public function gridAction()
    {
        $recordId  = (int) $this->getRequest()->getParam('id');
        $recordModel   = Mage::getModel('d1m_invitation/invitation');
        $record = $recordModel->load($recordId);

        
        if (!$record || !$record->getBrandId()) {
            return;
        }
        
        Mage::register('invitation', $record);
        Mage::register('current_invitation', $record);
        
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('d1m_invitation/adminhtml_invitation_edit_tab_history')->toHtml()
        );
    }

    public function saveAction()
    {

        if ($this->getRequest()->getPost()) 
        {
            
            $invitation = Mage::getModel('d1m_invitation/invitation');
            $invitationId = $this->getRequest()->getParam('id', false);
            if ($invitationId) {
	            $invitation->load($invitationId);
	        }

        	$postData = $this->_filterPostData($this->getRequest()->getPost());
		
	        if (!isset($postData['record'])) {
	            $this->_getSession()->addError(
	                Mage::helper('d1m_invitation')->__('Error while saving this invitation. Please try again later.')
	            );
	            $this->_redirect('*/*/edit', array('_current'=>true));
	            return;
	        }
	
	        $data = new Varien_Object($postData['record']);
	        
	        $invitation->setCustomerId($data->getCustomerId())
		               ->setStatus($data->getStatus())
		               ->setCouponCode($data->getCouponCode())
		               ->setName($data->getName())
		               ->setEmail($data->getEmail())
		               ->setPhone($data->getPhone())
		               ->setNote($data->getNote())
		            	;

	        try 
	        {
	            $invitation->save();
	            
	            $this->_getSession()->addSuccess(
	                Mage::helper('d1m_invitation')->__('Invitation was successfully saved.')
	            );
	            if ($this->getRequest()->getParam('_continue')) {
	                $this->_redirect('*/*/edit', array('_current'=>true, 'id'=>$invitation->getId()));
	            } else {
	                $this->_redirect('*/*/');
	            }
	        } catch (Exception $e) {
	            $this->_getSession()->addError($e->getMessage());
	            $this->_getSession()->setRecordData($invitation->getData());
	            $this->_redirect('*/*/edit', array('_current'=>true));
	        }
            
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        
        Mage::getSingleton('adminhtml/session')->addError( Mage::helper('d1m_invitation')->__('Invitation can not be deleted.'));
        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
           
        $this->_redirect('*/*/');
    }
    
    
    // mass action
    public function massDeleteAction()
    {
        Mage::getSingleton('adminhtml/session')->addError( Mage::helper('d1m_invitation')->__('Invitation can not be deleted.'));
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
