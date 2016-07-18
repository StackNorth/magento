<?php
/**
 * Created by Victor Guo
 * Date: 13-8-15
 * Time: 上午10:02
 */

class D1m_Slides_Adminhtml_D1mslidesController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('etam/d1m_slides/d1m_slides_slide')
            ->_addBreadcrumb($this->__('D1m Slides'), $this->__('Slide'));
        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('D1m Slides'))->_title('Slide');
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('d1m_slides/adminhtml_slides'));
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction(){
        $id = $this->getRequest()->getParam('id');
        if($id)
        {
            /*edit*/
            $model = Mage::getModel('d1m_slides/slide')->load($id);
            if($model->getId())
            {
                $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
                if(!empty($data))
                {
                    $model->setData($data);
                }

                Mage::register('slide_data', $model);
            }
            else
            {
                Mage::getSingleton('adminhtml/session')->addError($this->__('Slide does not exist'));
                $this->_redirect('*/*/');
            }
        }

        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('d1m_slides/adminhtml_slides_edit'))
            ->_addLeft($this->getLayout()->createBlock('d1m_slides/adminhtml_slides_edit_tabs'));
        $this->renderLayout();
    }

    public function saveAction()
    {
        $now = new DateTime('now');
        if($this->getRequest()->isPost())
        {
            $imageData = array();
            if(!empty($_FILES['filename']['name'])){
                try{
                    $fileName = $_FILES['filename']['name'];
                    $ext = substr($fileName, strrpos($fileName, '.') + 1);
                    $timestamp = time();
                    $newName = 'slide-'.$timestamp.'.'.$ext;

                    $uploader = new Varien_File_Uploader('filename');
                    $uploader->setAllowedExtensions(array(
                        'jpg', 'jpeg', 'gif', 'png'
                    ));
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(false);

                    $path = Mage::getBaseDir('media').DS.'custom'.DS.'slides';
                    $uploader->save($path, $newName);

                    $imageData['filename'] = 'custom/slides/'.$newName;
                }
                catch(Exception $ex){
                    Mage::getSingleton('adminhtml/session')->addError($ex->getMessage());
                    $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                    return;
                }
            }

            $data = $this->getRequest()->getPost();

            if(!empty($imageData['filename']))
            {
                $data['filename'] = $imageData['filename'];
            }

            $model = Mage::getModel('d1m_slides/slide');
            $model->setData($data)
                  ->setId($this->getRequest()->getParam('id'));

            try
            {
                if($model->getCreatedTime() == null || $model->getUpdateTime() == null)
                {
                    $model->setCreatedTime($now->format('Y-m-d H:i:s'))
                          ->setUpdateTime($now->format('Y-m-d H:i:s'));
                }
                else
                {
                    $model->setUpdateTime($now->format('Y-m-d H:i:s'));
                }

                $model->save();
                Mage::getSingleton('adminhtml/session')
                    ->addSuccess($this->__('Slide was successfully saved'));

                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if($this->getRequest()->getParam('back'))
                {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                else
                {
                    $this->_redirect('*/*/');
                    return;
                }
            }
            catch(Exception $ex)
            {
                Mage::getSingleton('adminhtml/session')->addError($ex->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        else
        {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Unable to find item to save'));
            $this->_redirect('*/*/');
        }
    }

    public function deleteAction()
    {
        if($id = $this->getRequest()->getParam('id')>0)
        {
            try
            {
                $model = Mage::getModel('d1m_slides/slide')->load($id);
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Slide was successfully deleted'));
                $this->_redirect('*/*/');
            }
            catch(Exception $ex)
            {
                Mage::getSingleton('adminhtml/session')->addError($ex->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        else
        {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Unable to find the Slide.'));
            $this->_redirect('*/*/');
        }
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('etam/d1m_slides/d1m_slides_slide');
    }
}