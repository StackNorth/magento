<?php

class D1m_Credits_Adminhtml_CreditsController extends Mage_Adminhtml_Controller_Action
{


    public function indexAction()
    {
        $this->loadLayout();

          $this->_setActiveMenu('etam/d1m_credits');
          $this->_addBreadcrumb(Mage::helper('d1m_credits')->__('Manage'), Mage::helper('d1m_credits')->__('Manage Credits'));
          $this->_addContent($this->getLayout()->createBlock('d1m_credits/adminhtml_credits_list'));

        $this->renderLayout();
    }


    public function exportCsvAction()
    {
        $fileName = 'credits.csv';
        $content = $this->getLayout()->createBlock('d1m_credits/adminhtml_credits_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName = 'credits.xml';
        $content = $this->getLayout()->createBlock('d1m_credits/adminhtml_credits_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
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

        $recordId = (int)$this->getRequest()->getParam('id');
        $recordModel = Mage::getModel('d1m_credits/credits');
        $record = $recordModel->load($recordId);

        Mage::register('credits', $record);
        Mage::register('current_credit', $record);

        $this->_setActiveMenu('etam/d1m_credits');
        $this->_addBreadcrumb(Mage::helper('d1m_credits')->__('Manage Credits'), Mage::helper('d1m_credits')->__('Manage Credits'));

        $this->_addContent($this->getLayout()->createBlock('d1m_credits/adminhtml_credits_edit'));
        $this->_addLeft($this->getLayout()->createBlock('d1m_credits/adminhtml_credits_edit_tabs'));
        $this->renderLayout();
    }

    public function gridAction()
    {

        $id = (int)$this->getRequest()->getParam('id');
        settype($id, "integer");
        if ($id <= 0) return;

        $recordId = $id;
        $recordModel = Mage::getModel('d1m_credits/credits');
        $record = $recordModel->load($recordId);


        Mage::register('credits', $record);
        Mage::register('current_credit', $record);

        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('d1m_credits/adminhtml_credits_edit_tab_history')->toHtml()
        );
    }

    public function saveOrder($payment, $uid, $giveQty, $credit_qty, $grandTotal, $order_from = '', $order_trench = '',$order_type='buy')
    {
        $order = Mage::getModel('d1m_credits/order');
        $creditCheckoutData = array();
        $creditCheckoutData['qty'] = $credit_qty;
        $creditCheckoutData['payment_method'] = $payment;
        $order->initOrderData($creditCheckoutData);
        $order->setCustomerId($uid);

        $order->setStatus(D1m_Credits_Model_Order::STATE_COMPLETE);
        $order->setGiftCredits($giveQty);
        $order->setGiftTotal($giveQty);
        $order->setOrderFrom($order_from);
        $order->setOrderTrench($order_trench);
        $order->setOrderType($order_type);
        // $order->setQty($credit_qty);
        // $grandTotal=$credit_qty+$giveQty;
        $order->setGrandTotal($grandTotal);

        $order->save();
    }

    public function saveAction()
    {

        if ($this->getRequest()->getPost()) {

            $credits = Mage::getModel('d1m_credits/credits');
            $creditsId = $this->getRequest()->getParam('id', false);
            $postData = $this->_filterPostData($this->getRequest()->getPost());
            if (!isset($postData['record'])) {
                $this->_getSession()->addError(
                    Mage::helper('d1m_credits')->__('Error while saving this credits. Please try again later.')
                );
                $this->_redirect('*/*/edit', array('_current' => true));
                return;
            }
           /* if ($creditsId) {
                $credits->load($creditsId);
            }*/
            $grandTotal = $postData['record']['subtotal'];//付款金额
            $giveQty = $postData['record']['give_num'];
            $credit_amount = $postData['record']['credit_amount'];
            $creditAmount = $credit_amount + $giveQty;
            $payment = $postData['record']['payment_method'];
            $order_trench = $postData['record']['order_trench'];
            $order_from = $postData['record']['order_from'];
            $order_type = $postData['record']['order_type'];
            $data = new Varien_Object($postData['record']);

            //check brand id
            $creditData= $credits->load($data->getCustomerId(),'customer_id');

            if ($creditData->getId()) {
                $totalAmount=$creditAmount+$creditData->getCreditAmount();
                $credits->setId($creditData->getId());
                $credits->setCreditAmount($totalAmount);
                $creditDate=Mage::getModel('core/date')->date();
                $credits->setUpdatedAt($creditDate);
            }else{
                $credits
                    ->setCustomerId($data->getCustomerId())
                    ->setCreditAmount($creditAmount);
            }




            try {
                //$credits->historyOrderNo =  '1111';
                if ($creditsId) {
                    $credits->historyDesc = 'change it from the backend directly.';
                } else {
                    $credits->historyDesc = 'give it from the backend directly.';
                }

                $credits->save();
                if (!$creditsId) {
                    $this->saveOrder($payment, $data->getCustomerId(), $giveQty, $data->getCreditAmount(), $grandTotal, $order_from, $order_trench,$order_type);
                    //  $this->saveOrder($data->getCreditAmount(), $payment, $data->getCustomerId());
                }
                $this->_getSession()->addSuccess(
                    Mage::helper('d1m_credits')->__('Credits was successfully saved.')
                );
                if ($this->getRequest()->getParam('_continue')) {
                    $this->_redirect('*/*/edit', array('_current' => true, 'id' => $credits->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->setRecordData($credits->getData());
                $this->_redirect('*/*/edit', array('_current' => true));
            }

        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {

        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('d1m_credits')->__('Credits can not be deleted.'));
        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));

        $this->_redirect('*/*/');
    }


    // mass action
    public function massDeleteAction()
    {
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('d1m_credits')->__('Credits can not be deleted.'));
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
        if (isset($data['record'])) {
            $_data = $data['record'];
            //$_data = $this->_filterDateTime($_data, array('start_time', 'end_time'));
            $data['record'] = $_data;
        }
        return $data;
    }

}    
