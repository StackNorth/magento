<?php
class D1m_Integral_Adminhtml_IntegralController extends Mage_Adminhtml_Controller_Action
{
   
    
    public function indexAction()
    {                
        $this->loadLayout();

        $this->_setActiveMenu('etam/d1m_integral');
        $this->_addBreadcrumb(Mage::helper('d1m_integral')->__('Manage'), Mage::helper('d1m_integral')->__('Manage Integral'));
        $this->_addContent($this->getLayout()->createBlock('d1m_integral/adminhtml_integral_list'));

        $this->renderLayout();
    }
    

	
	public function exportCsvAction()
    {
        $fileName   = 'integral.csv';
        $content    = $this->getLayout()->createBlock('d1m_integral/adminhtml_integral_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'integral.xml';
        $content    = $this->getLayout()->createBlock('d1m_integral/adminhtml_integral_grid')
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
        $recordModel   = Mage::getModel('d1m_integral/integral');
        $record = $recordModel->load($recordId);

        Mage::register('integral', $record);
        Mage::register('current_integral', $record);
        
        $this->_setActiveMenu('etam/d1m_integral');
        $this->_addBreadcrumb(Mage::helper('d1m_integral')->__('Manage Integral'), Mage::helper('d1m_integral')->__('Manage Integral'));

        $this->_addContent($this->getLayout()->createBlock('d1m_integral/adminhtml_integral_edit'));
        $this->_addLeft($this->getLayout()->createBlock('d1m_integral/adminhtml_integral_edit_tabs'));
        $this->renderLayout();
    }

    public function gridAction()
    {


        $id  = (int) $this->getRequest()->getParam('id');
        settype($id,"integer");
        if ($id<=0) return ;

        $recordId  = $id;
        $recordModel   = Mage::getModel('d1m_integral/integral');
        $record = $recordModel->load($recordId);





        Mage::register('integral', $record);
        Mage::register('current_integral', $record);
        // echo $record->getId(); die();

        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('d1m_integral/adminhtml_integral_edit_tab_history')->toHtml()
        );
    }

    public function saveAction()
    {

        if ($this->getRequest()->getPost()) 
        {
            
            $integral = Mage::getModel('d1m_integral/integral');
            $integralId = $this->getRequest()->getParam('id', false);
            if ($integralId) {
	            $integral->load($integralId);
	        }

        	$postData = $this->_filterPostData($this->getRequest()->getPost());
		
	        if (!isset($postData['record'])) {
	            $this->_getSession()->addError(
	                Mage::helper('d1m_integral')->__('Error while saving this integral. Please try again later.')
	            );
	            $this->_redirect('*/*/edit', array('_current'=>true));
	            return;
	        }
	
	        $data = new Varien_Object($postData['record']);
	        
	        //check brand id 
	        if($integral->checkCustomerIdExists($data->getCustomerId(), $integralId))
	        {
	        	$this->_getSession()->addError(
    					Mage::helper('d1m_integral')->__('Error while saving this integral. the credit for this customer is already exists.')
    			);
    			$this->_getSession()->setRecordData($data->getData());
    			$this->_redirect('*/*/edit', array('_current'=>true));
    			return;
	        }
	        
            
	        $integral
		        	->setCustomerId($data->getCustomerId())
		        	->setCreditAmount($data->getCreditAmount())
		            ;

	        try 
	        {
                if($integralId)
                {
                	$integral->historyDesc =  'change it from the backend directly.';
                }
                else
                {
                	$integral->historyDesc =  'give it from the backend directly.';
                }
                
	            $integral->save();
	            
	            $this->_getSession()->addSuccess(
	                Mage::helper('d1m_integral')->__('Integral was successfully saved.')
	            );
	            if ($this->getRequest()->getParam('_continue')) {
	                $this->_redirect('*/*/edit', array('_current'=>true, 'id'=>$integral->getId()));
	            } else {
	                $this->_redirect('*/*/');
	            }
	        } catch (Exception $e) {
	            $this->_getSession()->addError($e->getMessage());
	            $this->_getSession()->setRecordData($integral->getData());
	            $this->_redirect('*/*/edit', array('_current'=>true));
	        }
            
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        
        Mage::getSingleton('adminhtml/session')->addError( Mage::helper('d1m_integral')->__('Integral can not be deleted.'));
        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
           
        $this->_redirect('*/*/');
    }
    
    
    // mass action
    public function massDeleteAction()
    {
        Mage::getSingleton('adminhtml/session')->addError( Mage::helper('d1m_integral')->__('Integral can not be deleted.'));
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
