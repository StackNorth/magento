<?php
/**
 * Created by Victor Guo
 * Date: 13-8-15
 * Time: 上午10:47
 */
class D1m_Slides_Adminhtml_D1mslidesgroupController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('etam/d1m_slides/d1m_slides_slide')
            ->_addBreadcrumb($this->__('Slides Group Manager'),
            $this->__('Slides Group Manager'));
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('d1m_slides/adminhtml_groups'));
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    private function _getRelatedSlides($id)
    {
        $relatedSlides = Mage::getModel('d1m_slides/mapping')
            ->getCollection()
            ->addFieldToFilter('group_id', $id);
        if(count($relatedSlides)>0)
        {
            $resultArray = array();
            foreach($relatedSlides as $slide)
            {
                $resultArray[] = $slide->getSlideId();
            }
            $resultStr = implode(',', $resultArray);
            Mage::register('groupslides_data', $resultStr);
            return $resultStr;
        }
        else
        {
            return '';
        }
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        if($id)
        {
            $model = Mage::getModel('d1m_slides/group')->load($id);
            if($model->getId())
            {
                $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
                if(!empty($data))
                {
                    $model->setData($data);
                }
                $model['selected_slide_ids'] = $this->_getRelatedSlides($id);
                Mage::register('group_data', $model);
            }
            else
            {
                Mage::getSingleton('adminhtml/session')->addError($this->__('Group does not exist'));
                $this->_redirect('*/*/');
            }
        }

        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('d1m_slides/adminhtml_groups_edit'))
            ->_addLeft($this->getLayout()->createBlock('d1m_slides/adminhtml_groups_edit_tabs'));
        $this->renderLayout();
    }

    public function saveAction()
    {
        $now = new DateTime('now');
        if($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost();

            $model = Mage::getModel('d1m_slides/group');
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

                if($this->getRequest()->getParam('id'))
                {
                    $oldMappings = Mage::getModel('d1m_slides/mapping')->getCollection()
                        ->addFieldToFilter('group_id', $this->getRequest()->getParam('id'));
                    foreach($oldMappings as $mapping)
                    {
                        $mapping->delete();
                    }
                }

                $model->save();

                $selectedSlides = $this->getRequest()->getPost('selected_slide_ids');

                foreach(explode(',', $selectedSlides) as $slideId)
                {
                    if(!empty($slideId))
                    {
                        $mapModel = Mage::getModel('d1m_slides/mapping');
                        $mapModel->setGroupId($model->getId());
                        $mapModel->setSlideId($slideId);
                        $mapModel->save();
                    }
                }

                Mage::getSingleton('adminhtml/session')
                    ->addSuccess($this->__('Group was successfully saved'));

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
                $model = Mage::getModel('d1m_slides/group')->load($id);
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Group was successfully deleted'));
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
            Mage::getSingleton('adminhtml/session')->addError($this->__('Unable to find the Group.'));
            $this->_redirect('*/*/');
        }
    }

    public function slidesgridAction()
    {
        $this->_initAction();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('d1m_slides/adminhtml_groups_edit_tab_grid')->toHtml()
        );
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('etam/d1m_slides/d1m_slides_group');
    }
}