<?php

/**
 * Robi_Checkout_Block_Orderedclasses
 */
class Robi_Checkout_Block_Orderedclasses extends Mage_Core_Block_Template
{
	protected $_customer = null;
    protected $_checkout = null;
    protected $_quote    = null;
        
     public function __construct()
    {
        parent::__construct();
        
    }
    
    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }
    
    
    public function canCancel($status)
    {
    	return $status == 'pending';
    }
    
    public function getCancelUrl($order_id)
    {
        return $this->getUrl('sales/order/cancel', array('order_id' => $order_id));
    }
    
    
    public function getAttributeText($attributeCode, $attributeValue)
    {
        return Mage::getModel('catalog/product')->getResource()
            ->getAttribute($attributeCode)
                ->getSource()
                    ->getOptionText($attributeValue);
    }
    
    protected function _getOrderClassesCollection()
    {
        if (is_null($this->_productCollection)) {
        	
        	
        	$resource = Mage::getSingleton('core/resource');
        	$order_table = $resource->getTableName('sales/order');
            $attribute_option_Table=$resource->getTableName('eav_attribute_option_value');
        	$_productCollection = Mage::getModel('sales/order_item')
        								->getCollection();
        	
        	$productResource = Mage::getResourceSingleton('catalog/product');
	        $classdateAttr = $productResource->getAttribute('class_date');
	        $classdateAttrId = $classdateAttr->getAttributeId();
	        $classdateAttrTable = $classdateAttr->getBackend()->getTable();
	        
	        
	        $classaddressAttr = $productResource->getAttribute('class_address');
	        $classaddressAttrId = $classaddressAttr->getAttributeId();
	        $classaddressAttrTable = $classaddressAttr->getBackend()->getTable();

            //province 属性 global 全球
            $provinceAttr = $productResource->getAttribute('province');
            $provinceAttrId = $provinceAttr->getAttributeId();
            $provinceAttrTable = $provinceAttr->getBackend()->getTable();

        	$storeId = Mage::app()->getStore()->getId();

            // 只显示支付成功的订单
        	$_productCollection->getSelect()->joinInner(
								                array('_order' => $order_table),
								                '_order.entity_id=main_table.order_id 
								                    AND (_order.customer_id = '.(int)$this->getCustomer()->getId().')
								                    AND (_order.status="complete" or _order.status="processing") ',
								               array('order_increment_id'=>'_order.increment_id','order_status'=>'_order.status')
								            )
                                            ->joinInner(
									                array('_table_product_classdate' => $classdateAttrTable),
									                '_table_product_classdate.entity_id=main_table.product_id 
									                   
									                    AND _table_product_classdate.attribute_id = '.(int)$classdateAttrId .'
									                    AND _table_product_classdate.value >= \''.date('Y-m-d').'\'', 
									               array('classdate'=>'_table_product_classdate.value')
									            )
                                            ->joinInner(
                                                array( '_table_product_province' => $provinceAttrTable  ),
                                                ' _table_product_province.entity_id=main_table.product_id
                                                      AND (_table_product_province.store_id = 0)
                                                     AND _table_product_province.attribute_id = '.(int)$provinceAttrId,
                                                array('product_province'=>'_table_product_province.value' )
                                            )
                                            ->joinInner(
                                                array('_table_option'=>$attribute_option_Table ),
                                                '_table_option.store_id=0
                                                 and _table_option.option_id=_table_product_province.value',
                                                array( 'product_province_label'=>'_table_option.value' )
                                            )

								            ->joinLeft(
								                array('_table_product_lassaddress' => $classaddressAttrTable),
								                '_table_product_lassaddress.entity_id=main_table.product_id 
								                    AND (_table_product_lassaddress.store_id = '.$storeId.')
								                    AND _table_product_lassaddress.attribute_id = '.(int)$classaddressAttrId, 
								               array('product_store_id'=>'_table_product_lassaddress.store_id')
								            )->joinLeft(
								                array('_table_product_lassaddress2' => $classaddressAttrTable),
								                '_table_product_lassaddress2.entity_id = main_table.product_id
								                    AND (_table_product_lassaddress2.store_id = 0)
								                    AND _table_product_lassaddress2.attribute_id = '.(int)$classaddressAttrId, 
								               array('')
								            )
								            ->from("",array(
								                        'product_lassaddress2' => "_table_product_lassaddress2.value",
								                        'product_lassaddress1' => "_table_product_lassaddress.value",
								                        //'product_lassaddress' => new Zend_Db_Expr('IFNULL(_table_product_lassaddress.value,_table_product_lassaddress2.value)')
                                                        'product_lassaddress' => new Zend_Db_Expr('IF(_table_product_lassaddress.value_id>0,_table_product_lassaddress.value,_table_product_lassaddress2.value)')
								                        ))
                                            ->order('_table_product_classdate.value')

            ;



            
//即将开始的课程
           //  echo $_productCollection->getSelect();            die();

        	$this->_productCollection = $_productCollection;
        }
        
        return $this->_productCollection;
        
    }

}