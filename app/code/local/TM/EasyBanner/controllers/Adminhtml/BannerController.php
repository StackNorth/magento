<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 * 
 * EasyBanner module for Magento - flexible banner management
 * 
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_EasyBanner_Adminhtml_BannerController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('templates_master/easybanner/banner')
            ->_addBreadcrumb(Mage::helper('easybanner')->__('Templates Master'), Mage::helper('easybanner')->__('Templates Master'))
            ->_addBreadcrumb(Mage::helper('easybanner')->__('Easy Banner'), Mage::helper('easybanner')->__('Easy Banner'))
            ->_addBreadcrumb(Mage::helper('easybanner')->__('Banner Manager'), Mage::helper('easybanner')->__('Banner Manager'));
        return $this;
    }
    
    /**
     * Banner list page
     */
    public function indexAction()
    {
        $this->_initAction();       
        $this->_addContent($this->getLayout()->createBlock('easybanner/adminhtml_banner'));
        $this->renderLayout();
    }
    
    /**
     * Create new banner
     */
    public function newAction()
    {
        $this->_forward('edit');
    }
    
    /**
     * Banner edit form
     */
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('easybanner/banner');
        
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('easybanner')->__('This banner no longer exists'));
                $this->_redirect('*/*/');
                return;
            }
        }
        
        $data = Mage::getSingleton('adminhtml/session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $model->getConditions()->setJsFormObject('banner_conditions_fieldset');
        
        Mage::register('easybanner_banner', $model);
        
        $this->_initAction();
        
        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->setCanLoadRulesJs(true);

        $this
            ->_addBreadcrumb($id ? Mage::helper('easybanner')->__('Edit Banner') : Mage::helper('easybanner')->__('New Banner'), $id ? Mage::helper('easybanner')->__('Edit Banner') : Mage::helper('easybanner')->__('New Banner'))
            ->_addContent($this->getLayout()->createBlock('easybanner/adminhtml_banner_edit')->setData('action', $this->getUrl('*/*/save')))
            ->_addLeft($this->getLayout()->createBlock('easybanner/adminhtml_banner_edit_tabs'))
            ->renderLayout();
    }
    
    /**
     * Banner grid for AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('easybanner/adminhtml_banner_grid')->toHtml()
        );
    }
    
    public function clearStatisticsAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            Mage::getResourceModel('easybanner/banner_statistic')->clearStatistics($id);
        }
    }
    
    /**
     * Save banner
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            try {
                $model = Mage::getModel('easybanner/banner');
                
                $data['conditions'] = $data['rule']['conditions'];
                unset($data['rule']);
                
                if (isset($data['placeholder_ids']) && is_array($data['placeholder_ids'])) {
                    $model->setPlaceholderIds($data['placeholder_ids']);
                }
                
                $model->loadPost($data);
                Mage::getSingleton('adminhtml/session')->setPageData($model->getData());
                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('easybanner')->__('Banner was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setPageData(false);
                
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setPageData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }
    
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('easybanner/banner');
                $model->load($id);
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('easybanner')->__('Banner was successfully deleted'));
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $id));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('easybanner')->__('Unable to find a banner to delete'));
        $this->_redirect('*/*/');
    }
    
    public function newConditionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('easybanner/banner'))
            ->setPrefix('conditions');
        
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

    public function chooserAction()
    {
        $block = false;
        switch ($this->getRequest()->getParam('attribute')) {
            case 'product_ids':
                $block = $this->getLayout()->createBlock(
                    'easybanner/adminhtml_widget_chooser_product', 'easybanner_widget_chooser_product',
                    array('js_form_object' => $this->getRequest()->getParam('form'),
                ));
                break;

            case 'category_ids':
                $block = $this->getLayout()->createBlock(
                        'adminhtml/catalog_category_checkboxes_tree', 'easybanner_widget_chooser_category_ids',
                        array('js_form_object' => $this->getRequest()->getParam('form'))
                    )->setCategoryIds($this->getRequest()->getParam('selected', array()));
                break;
                
            case 'handle':
                $block = $this->getLayout()->createBlock(
                    'easybanner/adminhtml_widget_chooser_handle', 'easybanner_widget_chooser_handle',
                    array('js_form_object' => $this->getRequest()->getParam('form'),
                ));
                break;
                
            case 'customer_group':
                $block = $this->getLayout()->createBlock(
                    'easybanner/adminhtml_widget_chooser_customerGroup', 'easybanner_widget_chooser_customer_group',
                    array('js_form_object' => $this->getRequest()->getParam('form'),
                ));
                break;
        }
        if ($block) {
            $this->getResponse()->setBody($block->toHtml());
        }
    }
    
    /**
     * Get tree node (Ajax version)
     */
    public function categoriesJsonAction()
    {
        if ($categoryId = (int) $this->getRequest()->getPost('id')) {
            $this->getRequest()->setParam('id', $categoryId);

            if (!$category = $this->_initCategory()) {
                return;
            }
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('adminhtml/catalog_category_tree')
                    ->getTreeJson($category)
            );
        }
    }

    /**
     * Initialize category object in registry
     *
     * @return Mage_Catalog_Model_Category
     */
    protected function _initCategory()
    {
        $categoryId = (int) $this->getRequest()->getParam('id',false);

        $storeId    = (int) $this->getRequest()->getParam('store');

        $category = Mage::getModel('catalog/category');
        $category->setStoreId($storeId);

        if ($categoryId) {
            $category->load($categoryId);
            if ($storeId) {
                $rootId = Mage::app()->getStore($storeId)->getRootCategoryId();
                if (!in_array($rootId, $category->getPathIds())) {
                    $this->_redirect('*/*/', array('_current'=>true, 'id'=>null));
                    return false;
                }
            }
        }

        Mage::register('category', $category);
        Mage::register('current_category', $category);
        return $category;
    }
}