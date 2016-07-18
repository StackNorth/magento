<?php
class D1m_Course_Block_Reorder extends Mage_Core_Block_Template
{
    public $order_id=null;
    public $order_qty=null;
    private function getInfo($productId)
    {
        /* @var $courses D1m_Course_Model_Mysql4_Course_Collection  */
        $courses =  Mage::getResourceModel('d1m_course/course_collection')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('class_address')
            ->addAttributeToSelect('province')
            ->addAttributeToSelect('class_date')
            ->addAttributeToSelect('n_classtime1')
            ->addAttributeToSelect('n_classtime2');
         $courses->addFieldToFilter('entity_id', $productId);
         $item=$courses->getFirstItem();
         $id=$item->getId();
        if ($id==null) return null;
        settype($id,"integer");
        if ($id<=0) return null;
         return $item;
    }
    public function getOldinfo()
    {
        //根据订单号取产品信息
        $id=Mage::getSingleton('customer/session')->getData('reorder_orderid');
        settype($id,"integer");
        if ($id<=0) return null;

        //检查订单id 取旧产品oid

        /* @var $model Mage_Sales_Model_Order */
        $model=Mage::getModel('sales/order');
        $model->load($id);
        $id=$model->getId();
        if ($id<=0) return null;
        //订单号
        $this->order_id=$model->getIncrementId();

        //要求是本人的订单
        if (Mage::getSingleton('customer/session')->getCustomerId()!=$model->getCustomerId()) return null;
        /* @var $collection Mage_Sales_Model_Resource_Order_Item_Collection */
        $collection = Mage::getResourceModel('sales/order_item_collection');
        $collection->addFieldToFilter('order_id',$id);
        //要求单一产品
        if ( $collection->getSize()!=1) return null;
        $item=$collection->getFirstItem();
        $oid=$item->getData('product_id');
        $this->order_qty=$item->getdata('qty_ordered');
        if ($oid==null) return null;
        settype($oid,"integer");
        if ($oid<=0)        return null;
        return $this->getInfo($oid);

    }
    public function getNewinfo()
    {
        $id=$this->getRequest()->getParam('nid',0);
        settype($id,"integer");
        if ($id<=0)        return null;
        return $this->getInfo($id);

    }

}