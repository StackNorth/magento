<?php
/**
 * Created by PhpStorm.
 * User: d1m
 * Date: 2016/7/14
 * Time: 14:28
 */
class D1m_Credits_Adminhtml_CredittestController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('etam/producttool')
            ->_addBreadcrumb($this->__('D1m Credits'), $this->__('Report'));
        return $this;
    }
    
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('etam/d1m_credits_test');
        $this->_addBreadcrumb(Mage::helper('d1m_credits')->__('Manage'), Mage::helper('d1m_credits')->__('Manage Credit Test'));
        $this->_addContent($this->getLayout()->createBlock('d1m_credits/adminhtml_credittest_list'));
        $this->renderLayout();
    }
    
    public function editAction(){
        $testId = (int)$this->getRequest()->getParam('id');
        $this->_initAction();
        $test = Mage::getModel('d1m_credits/test')->load($testId);
        Mage::register('credits_test', $test);
        $this->_title($this->__('测试修改'));
        $this->_addContent($this->getLayout()->createBlock('d1m_credits/adminhtml_credittest_edit'));
        //$this->_addLeft($this->getLayout()->createBlock('d1m_credits/adminhtml_credittest_edit_tabs'));;
        $this->renderLayout();


    }
    public function massDeleteAction()
    {

        $creditTestIdArray = $this->getRequest()->getParam('id', false);
        foreach($creditTestIdArray as $v)
        {
            $credittest = Mage::getModel('d1m_credits/test');
            $credittest->setId($v)->delete();

        }

       // Mage::getSingleton('adminhtml/session')->addError( Mage::helper('d1m_credits')->__('Credits can not be deleted.'));
        $this->_redirect('*/*/index');
    }
    public function newAction()
    {
        $this->_forward('edit');
    }

    public function saveAction()
    {

        if ($this->getRequest()->isPost()) {

            $credittest = Mage::getModel('d1m_credits/test');
            $creditTestId = $this->getRequest()->getParam('id', false);
            $postData = $this->_filterPostData($this->getRequest()->getPost());
            if (!isset($postData['record'])) {
                $this->_getSession()->addError(
                    Mage::helper('d1m_credits')->__('Error while saving this credits. Please try again later.')
                );
                $this->_redirect('*/*/edit', array('_current' => true));
                return;
            }
            var_dump($postData);

            /*
             * Image Deal Code start
             * */

            if(!empty($_FILES['filename']['name'])){
                try{
                    $fileName = $_FILES['filename']['name'];
                    $ext = substr($fileName, strrpos($fileName, '.') + 1);
                    $timestamp = time();
                    $newName = 'credits-'.$timestamp.'.'.$ext;
                    $uploader = new Varien_File_Uploader('filename');
                    $uploader->setAllowedExtensions(array(
                        'jpg', 'jpeg', 'gif', 'png'
                    ));
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(false);

                    $path = Mage::getBaseDir('media').DS.'custom'.DS.'credits';
                    $uploader->save($path, $newName);

                    $postData['record']['filename']['value'] = 'custom/credits/'.$newName;
                }
                catch(Exception $ex){
                    Mage::getSingleton('adminhtml/session')->addError($ex->getMessage());
                    $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                    return;
                }
            }

            /*Code end*/

            $Status   = $postData['record']['Status'];
            $email    = $postData['record']['email'];
            $title    = $postData['record']['title'];
            $content  = $postData['record']['content'];
            $time     = $postData['record']['time'];
            $img      = $postData['record']['filename']['value'];
            $testType = $postData['record']['test_type'];
            $data     = new Varien_Object($postData['record']);

          
            //check brand id

            $creditData= $credittest->load($creditTestId,'id');

            if ($creditData->getId()) {

                $credittest->setId($creditData->getId());
                $credittest->setStatus($Status);
                $credittest->setTitle($title);
                $credittest->setEmail($email);
                $credittest->setContent($content);
                $credittest->setTime($time);
                $credittest->setImg($img);
                $credittest->setType($testType);

            }else{
                $credittest->setId($data->getId())->setStatus($Status)->setContent($content)
                    ->setTime($time)->setEmail($email)->setTitle($title)->setType($testType)->setImg($img);
                ;


            }
            try {
                if ($creditTestId) {
                    $credittest->historyDesc = 'change it from the backend directly.';
                } else {
                    $credittest->historyDesc = 'give it from the backend directly.';
                }

                $credittest->save();

                $this->_getSession()->addSuccess(
                    Mage::helper('d1m_credits')->__('Credits was successfully saved.')
                );
                if ($this->getRequest()->getParam('_continue')) {
                    $this->_redirect('*/*/edit', array('_current' => true, 'id' => $credittest->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->setRecordData($credittest->getData());
                $this->_redirect('*/*/edit', array('_current' => true));
            }

        }
        $this->_redirect('*/*/');
        }

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