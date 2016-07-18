<?php

class D1m_Producttool_Adminhtml_OrderController extends Mage_Adminhtml_Controller_action
{

    /**
     * Export customer grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'd1m_producttool_order.csv';
        $content    = $this->getLayout()->createBlock('d1m_producttool/adminhtml_order_grid')
            ->getCsvFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export customer grid to XML format
     */
    public function exportXmlAction()
    {
        $fileName   = 'd1m_producttool_order.xml';
        $content    = $this->getLayout()->createBlock('d1m_producttool/adminhtml_order_grid')
            ->getExcelFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }


    protected function _initAction() {

		$this->loadLayout()
            ->_setActiveMenu('etam/producttool');
		return $this;
	}   
 
	public function indexAction()
    {


        $this->_initAction();
        $this->_initLayoutMessages('adminhtml/session');

        $this->getLayout()->getBlock('head')->setTitle($this->__("课程订购统计"));

        $block = $this->getLayout()->createBlock(
            'd1m_producttool/adminhtml_order',
            'producttool.order.list'
        );

         $this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
//		$this->_initAction()	->renderLayout();
	}


}