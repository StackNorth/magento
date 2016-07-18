<?php
class D1m_Credits_Adminhtml_CourseorderController extends Mage_Adminhtml_Controller_Action
{
   
    
    public function indexAction()
    {                
        $this->loadLayout();

        $this->_setActiveMenu('etam/d1m_credits_order');
        $this->_addBreadcrumb(Mage::helper('d1m_credits')->__('Manage'), Mage::helper('d1m_credits')->__('Manage Course Order'));
        $this->_addContent($this->getLayout()->createBlock('d1m_credits/adminhtml_courseorder_list'));

        $this->renderLayout();
    }
    

	
	public function exportCsvAction()
    {
        $fileName   = 'courseorders.csv';
        $content    = $this->getLayout()->createBlock('d1m_credits/adminhtml_courseorder_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'courseorders.xml';
        $content    = $this->getLayout()->createBlock('d1m_credits/adminhtml_courseorder_grid')
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
        $this->_forward('index');
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
