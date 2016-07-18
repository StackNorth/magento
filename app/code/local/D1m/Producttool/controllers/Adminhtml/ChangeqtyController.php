<?php
class D1m_Producttool_Adminhtml_ChangeqtyController extends Mage_Adminhtml_Controller_Action
{
    protected function _initHelper()
    {
        return Mage::helper('d1m_producttool');
    }


    public function indexAction()
    {
//        die('1234');
        //$where2= $this->getRequest()->getModuleName().':'.$this->getRequest()->getControllerName();
        //$where3= $where2.':'.$this->getRequest()->getActionName();
        //echo $where3;
        // producttool:adminhtml_producttool:index
        //die();

        //loadLayout只能一次


        $this->loadLayout()->_setActiveMenu('etam/producttool');
        $this->getLayout()->getBlock('head')->setTitle($this->__("调整课程库存数量或状态"));
        $this->_addContent($this->getLayout()->createBlock('d1m_producttool/adminhtml_changeqty_edit'));

        //$this->_getSession()->addError($e->getMessage());
        //$obj=$this->getLayout()->getMessagesBlock();        $obj->addError('abc'); $obj->addError('abcd');
        //Mage_Core_Model_Message_Collection
        $this->renderLayout();
    }


    public function saveAction()
    {
        //根据城市，日期，时间等筛选产品
        //修改status ,stock_qty
        $pday=$this->getRequest()->getParam('pday','');
        if (!is_array($pday) or (count($pday)==0))
        {
            $error='请指定课程日期';
            Mage::getSingleton('adminhtml/session')->addError($error);
            Mage::getSingleton('adminhtml/session')->setProducttoolData($data);
            $this->getResponse()->setRedirect($this->getUrl("*/*/"));
            return;
        }


        $data  =  $this->getRequest()->getPost();
        $status=$this->getRequest()->getParam('status','');
        if  (($status!='1') and ($status!='2')) $status='';

        $pqty=$this->getRequest()->getParam('pqty','');
        if ($pqty!='')
        {
             if (!preg_match('/^[0-9]{1,10}$/',$pqty))
             {
                 $error='请输入有效的库存数或留空';
                 Mage::getSingleton('adminhtml/session')->addError($error);
                 Mage::getSingleton('adminhtml/session')->setProducttoolData($data);
                 $this->getResponse()->setRedirect($this->getUrl("*/*/"));
                 return;
             }
        }
        if (($pqty=='') and ($status==''))
        {
            //do nothing
            Mage::getSingleton('adminhtml/session')->setProducttoolData($data);
            $this->getResponse()->setRedirect($this->getUrl("*/*/"));
            return;
        }



        //Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        /* @var $collection  Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('catalog/product_collection');
        //$collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());
        //$collection->setStoreId($storeId)  ->addStoreFilter($storeId);
        $ptime1=$this->getRequest()->getParam('ptime1','00:00');
        $ptime2=$this->getRequest()->getParam('ptime2','23:59');
        if (($ptime2!='23:59') and ($ptime1!='00:00'))
        {
            if ($ptime1!='00:00')   $collection->addAttributeToFilter('n_classtime1',array('gteq'=>$ptime1));
            if ($ptime2!='23:59')   $collection->addAttributeToFilter('n_classtime2',array('lteq'=>$ptime2));
        }
        $pcity=$this->getRequest()->getParam('pcity','');
        if ($pcity1="")
            $collection->addAttributeToFilter('province',$pcity);
        //pday 是数组
        $collection->addAttributeToFilter('class_date',$pday);
        $total=$collection->getSize();
        if ($total==0)
        {
            $error='没有发现要调整的课程';
            Mage::getSingleton('adminhtml/session')->addError($error);
            Mage::getSingleton('adminhtml/session')->setProducttoolData($data);
            $this->getResponse()->setRedirect($this->getUrl("*/*/"));
            return;
        }

        /* @var $product Mage_Catalog_Model_Product */
        set_time_limit(0); //long time execute
        $num1=0;
        $num2=0;
        foreach($collection as $product)
        {
            $product_id=$product->getId();
            if ($status!="")
            {
                $product->setData('status',$status);
                $product->save();
                ++$num1;
            }
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id);
            $stockItemId = $stockItem->getId();
            if ($stockItemId)
            {
                $stockItem->setData('qty', $pqty);
                if ($pqty>0)
                  $stockItem->setData('is_in_stock', 1);
                else
                  $stockItem->setData('is_in_stock', 0);
                $stockItem->save();
                ++$num2;
            }
        }
        if (($num1>0) and ($num2>0)) $msg=$num1.'个产品被调整';
        else if ( $num1>0 ) $msg=$num1.'个产品被调整状态';
        else if ( $num2>0 ) $msg=$num2.'个产品被调整库存';
        else $msg='?';

        Mage::getSingleton('adminhtml/session')->addSuccess($msg);
        $this->getResponse()->setRedirect($this->getUrl("*/*/"));

    }

}
