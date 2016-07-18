<?php
class D1m_Chef_Adminhtml_ChefController extends Mage_Adminhtml_Controller_Action {

    public function preDispatch() {
        parent::preDispatch();
    }

    protected function _initHelper(){
        return Mage::helper('d1m_chef');
    }

    protected function _initAction() {
        $this->loadLayout()->_setActiveMenu('etam/chef');
        return $this;
    }

    public function indexAction() {
        $this->_initAction();
        $this->_initLayoutMessages('adminhtml/session');

        $this->getLayout()->getBlock('head')->setTitle($this->__("厨师管理"));

        $block = $this->getLayout()->createBlock(
            'd1m_chef/adminhtml_chef',
            'chef.chef.list'
        );

        $this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function editAction() {



        $id              =     $this->getRequest()->getParam('id');
        $chefChef      =     Mage::getModel('d1m_chef/chef')->load($id);

        if ($chefChef->getId() || $id == 0)
        {
            $data = $this->_getSession()->getFormData(true);
            if (!empty($data)) {
                $chefChef->setData($data);
            }

            Mage::register('chef_chef_data', $chefChef);

            $this->_initAction()
                ->_addContent($this->getLayout()->createBlock('d1m_chef/adminhtml_chef_edit'))
                ->_addLeft($this->getLayout()->createBlock('d1m_chef/adminhtml_chef_edit_tabs'));

            $this->renderLayout();

        }
        else
        {
            $this->_initAction();
            $this->_getSession()->addError($this->__('The Chef Does Not Exist'));
            $this->_redirect('*/*/');
        }
    }

    public function saveAction() {
        // check if data sent
        if ($this->getRequest()->getPost())
        {

            $data            =   $this->getRequest()->getPost();
            $chefModel    =   Mage::getModel('d1m_chef/chef');

            //上传图片以及删除图片,for list image thumbnail
            if (isset($_FILES['csmallpic']['name']) and (file_exists($_FILES['csmallpic']['tmp_name'])))
            {
                $data['csmallpic'] = $this->_initHelper()->uploadFile($_FILES,$chefModel);
            }
            else
            {
                if (isset($data['csmallpic']['delete']) && $data['csmallpic']['delete'] == 1) {
                    $thumbnail_path = Mage::getBaseDir('media').DS. $data['csmallpic']['value'];
                    if (file_exists($thumbnail_path))@unlink($thumbnail_path);
                    $data['csmallpic'] = '';
                } else {
                    unset($data['csmallpic']);
                }
            }

            //上传图片以及删除图片,for detail content image
            if (isset($_FILES['cbigpic']['name']) and (file_exists($_FILES['cbigpic']['tmp_name']))) {
                $data['cbigpic'] = $this->_initHelper()->uploadFileForThumbnailMain($_FILES,$chefModel);
            } else {
                if (isset($data['cbigpic']['delete']) && $data['cbigpic']['delete'] == 1) {
                    $thumbnail_main_path = Mage::getBaseDir('media').DS. $data['cbigpic']['value'];
                    if (file_exists($thumbnail_main_path))@unlink($thumbnail_main_path);
                    $data['cbigpic'] = '';
                } else {
                    unset($data['cbigpic']);
                }
            }




            $chefModel->setData($data);

            $chefModel->save();

            try
            {
                $this->_getSession()->addSuccess($this->_initHelper()->__('保存成功'));
                $this->_getSession()->setFormData(false);

                //save and continue
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $chefModel->getId()));
                    return;
                }

                // $this->_redirect('*/*/');
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
            catch (Exception $e)
            {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction() {


        if ($id = $this->getRequest()->getParam('id')) {
            try
            {

                /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
                $collection = Mage::getResourceModel('catalog/product_collection');
                //$storeId = Mage::app()->getStore()->getId();
                //$collection->setStoreId($storeId) ->addStoreFilter($storeId);

//      $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());
                // $collection->addAttributeToSelect('*');
                $collection->addAttributeToSelect('chef');
                $collection->addAttributeToFilter('chef',$id);
                if ($collection->getFirstItem()!=null)
                {
                    //$data   =  $this->getRequest()->getPost();
                    $this->_getSession()->addError('当前厨师在课程中已经使用，不能删除。要删除必须先删除相关的课程');
                    //$this->_getSession()->setFormData($data);
                    //$this->_redirect('*/*/'); //在编辑界面没有显示提示信息
                    $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                    return;
                }




                $chefModel = Mage::getModel('d1m_chef/chef');
                $chefModel->load($id);
                $chefModel->delete();

                $this->_getSession()->addSuccess($this->_initHelper()->__('删除成功'));
                $this->_redirect('*/*/');
            } catch (Exception $e)
            {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $id));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {

        $chefIds = $this->getRequest()->getParam('chef_id');
        if (!is_array($chefIds)) {
            $this->_getSession()->addError($this->_initHelper()->__('请选取要删除的记录'));
        }
        else
        {
            try
            {
                $ndeleted=0;
                $nskip=0;

                /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
                $collection = Mage::getResourceModel('catalog/product_collection');
                //$storeId = Mage::app()->getStore()->getId();
                //$collection->setStoreId($storeId) ->addStoreFilter($storeId);
                $collection->addAttributeToSelect('chef');
                $collection->distinct(true);
                $arrchef=array();
                foreach ($collection as $item)
                {
                    $arrchef[]=$item->getData('chef');
                }

                foreach ($chefIds as $chefId)
                {
                    //检查是否用过
                   if (in_array($chefId,$arrchef)) {$nskip++; continue;}

                    $model = Mage::getModel('d1m_chef/chef')->load($chefId);
                    $model->delete();
                    $ndeleted++;
                }
                $this->_getSession()->addSuccess($this->_getHelper()->__('%d条记录被删除', $ndeleted));
                if ($nskip>0)
                    $this->_getSession()->addSuccess($this->_getHelper()->__('%d条记录没有删除，因为被已有课程使用', $nskip));

            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }

    public function massStatusAction() {
        $chefIds = $this->getRequest()->getParam('chef_id');

        if (!is_array($chefIds)) {
            $this->_getSession()->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($chefIds as $chefId) {
                    $model = Mage::getModel('d1m_chef/chef')
                        ->setId($chefId) //chef_status
                        ->setCstatus($this->getRequest()->getParam('chef_status'))
                        ->save();
                }
                $this->_getSession()
                    ->addSuccess($this->__('%d条记录成功更新', count($chefIds)));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

}
