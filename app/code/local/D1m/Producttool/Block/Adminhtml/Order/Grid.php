<?php

class D1m_Producttool_Block_Adminhtml_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('orderGrid');
      $this->setDefaultSort('product_id');
      //$this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
     //$this->setUseAjax(true);
  }

    protected function _prepareCollection()
    {

/*
 SELECT aca_sales_flat_order_item.product_id,
 SUM(aca_sales_flat_order_item.qty_ordered) AS `item_count`,
 COUNT(*) AS order_count,
 SUM(main_table.`grand_total`) AS order_sum,
 aca_sales_flat_order_item.name AS `name`,
 `_table_product_classdate`.`value` AS `classdate`,
 `_table_product_nclasstime1`.`value` AS `nclasstime1`,
 `_table_product_nclasstime2`.`value` AS `nclasstime2`,
 `_table_product_classaddress`.`value` AS `class_address`,
 `_table_product_classprovince`.`value` AS `province`
 FROM `aca_sales_flat_order_grid` AS `main_table`
 INNER JOIN `aca_sales_flat_order_item`  ON aca_sales_flat_order_item.order_id = `main_table`.entity_id AND main_table.status='complete'
 LEFT JOIN `aca_catalog_product_entity_datetime` AS `_table_product_classdate`  ON _table_product_classdate.entity_id=aca_sales_flat_order_item.product_id   AND (_table_product_classdate.store_id = 0) AND _table_product_classdate.attribute_id = 137
 LEFT JOIN `aca_catalog_product_entity_varchar` AS `_table_product_nclasstime1`  ON _table_product_nclasstime1.entity_id=aca_sales_flat_order_item.product_id AND (_table_product_nclasstime1.store_id = 0) AND _table_product_nclasstime1.attribute_id = 147
 LEFT JOIN `aca_catalog_product_entity_varchar` AS `_table_product_nclasstime2` ON _table_product_nclasstime2.entity_id=aca_sales_flat_order_item.product_id AND (_table_product_nclasstime2.store_id = 0)  AND _table_product_nclasstime2.attribute_id = 148
 LEFT JOIN `aca_catalog_product_entity_varchar` AS `_table_product_classaddress` ON _table_product_classaddress.entity_id=aca_sales_flat_order_item.product_id AND (_table_product_classaddress.store_id = 0) AND _table_product_classaddress.attribute_id = 139
 LEFT JOIN `aca_catalog_product_entity_int` AS `_table_product_classprovince` ON _table_product_classprovince.entity_id=aca_sales_flat_order_item.product_id AND (_table_product_classprovince.store_id = 0) AND _table_product_classprovince.attribute_id = 140

 GROUP BY aca_sales_flat_order_item.product_id
 */
        /* @var $collection D1m_Adminhtml_Model_Sales_Order_Grid_Collection */
        $collection=Mage::getModel('d1m_adminhtml/sales_order_grid_collection');
        //grid entity_id重复
        //$collection->addAttributeToSelect('*');
//查看不同的attribute....

        // Mage_Sales_Model_Resource_Order_Grid_Collection echo get_class($collection);die();
        $collection->setD1mSpecial(true); //需要重写计数

        //注意表前缀，应取配置resources/db/table_prefix
        //$xml = simplexml_load_file('./app/etc/local.xml', NULL, LIBXML_NOCDATA);
        //$pref = $xml->global->resources->db->table_prefix;

        $resource = Mage::getSingleton('core/resource');
        $itemTable = $resource->getTableName('sales_flat_order_item');
        $orderTable=$resource->getTableName('sales_flat_order');

        $collection->getSelect()
            ->reset('columns')
            ->join($itemTable,$itemTable.'.order_id = `main_table`.entity_id and main_table.status=\'complete\'',
            array(
                'sum('.$itemTable.'.qty_ordered) as qty_sum',
                'count(*) AS order_count',
                'sum(main_table.`grand_total`) AS order_sum',
                $itemTable.'.product_id as product_id',$itemTable.'.name as name')
        )
        // 课点支付金额
            ->join($orderTable,$orderTable.'.entity_id = `main_table`.entity_id',
                array(
                    'sum(-'.$orderTable.'.credit_amount) AS credit_amount_sum',
                    )
            );


        $collection->getSelect()->group($itemTable.'.product_id');

        //product_id qty_sum order_count order_sum
        //上课日期 ，时间， 城市


        $productResource = Mage::getResourceSingleton('catalog/product');


        $classdateAttr = $productResource->getAttribute('class_date');
        $classdateAttrId = $classdateAttr->getAttributeId();
        $classdateAttrTable = $classdateAttr->getBackend()->getTable();


        $nclasstime1Attr = $productResource->getAttribute('n_classtime1');
        $nclasstime1AttrId = $nclasstime1Attr->getAttributeId();
        $nclasstime1AttrTable = $nclasstime1Attr->getBackend()->getTable();

        $nclasstime2Attr = $productResource->getAttribute('n_classtime2');
        $nclasstime2AttrId = $nclasstime2Attr->getAttributeId();
        $nclasstime2AttrTable = $nclasstime2Attr->getBackend()->getTable();


        $classprovinceAttr = $productResource->getAttribute('province');
        $classprovinceAttrId = $classprovinceAttr->getAttributeId();
        $classprovinceAttrTable = $classprovinceAttr->getBackend()->getTable();
        
        $classaddressAttr = $productResource->getAttribute('class_address');
        $classaddressAttrId = $classaddressAttr->getAttributeId();
        $classaddressAttrTable = $classaddressAttr->getBackend()->getTable();

        $seatsAttr = $productResource->getAttribute('seats');
        $seatsAttrId = $seatsAttr->getAttributeId();
        $seatsAttrTable = $seatsAttr->getBackend()->getTable();

        $stockTable = $resource->getTableName('cataloginventory_stock_item');

        $collection->getSelect()

            ->joinleft(
                array('_table_product_classdate' => $classdateAttrTable),
                '_table_product_classdate.entity_id='.$itemTable.'.product_id
                    AND (_table_product_classdate.store_id = 0)
                    AND _table_product_classdate.attribute_id = '.(int)$classdateAttrId,
                array('classdate'=>'_table_product_classdate.value')        )
            ->joinleft(
                array('_table_product_nclasstime1' => $nclasstime1AttrTable),
                '_table_product_nclasstime1.entity_id='.$itemTable.'.product_id
                    AND (_table_product_nclasstime1.store_id = 0)
                    AND _table_product_nclasstime1.attribute_id = '.(int)$nclasstime1AttrId,
                array('nclasstime1'=>'_table_product_nclasstime1.value')        )
            ->joinleft(
                array('_table_product_nclasstime2' => $nclasstime2AttrTable),
                '_table_product_nclasstime2.entity_id='.$itemTable.'.product_id
                    AND (_table_product_nclasstime2.store_id = 0)
                    AND _table_product_nclasstime2.attribute_id = '.(int)$nclasstime2AttrId,
                array('nclasstime2'=>'_table_product_nclasstime2.value')        )
            ->joinleft(
                array('_table_product_seats' => $seatsAttrTable),
                '_table_product_seats.entity_id='.$itemTable.'.product_id
                    AND (_table_product_seats.store_id = 0)
                    AND _table_product_seats.attribute_id = '.(int)$seatsAttrId,
                array('seats'=>'_table_product_seats.value')        )


            ->joinleft(
                array('_table_product_stock' => $stockTable),
                '_table_product_stock.product_id='.$itemTable.'.product_id',
                array('qty'=>'_table_product_stock.qty')    )

//省份取的是编号
            ->joinleft(
                array('_table_product_classprovince' => $classprovinceAttrTable),
                '_table_product_classprovince.entity_id='.$itemTable.'.product_id
                    AND (_table_product_classprovince.store_id = 0)
                    AND _table_product_classprovince.attribute_id = '.(int)$classprovinceAttrId,
                array('province'=>'_table_product_classprovince.value')        )
                
            ->joinleft(
                array('_table_product_classaddress' => $classaddressAttrTable),
                '_table_product_classaddress.entity_id='.$itemTable.'.product_id
                    AND (_table_product_classaddress.store_id = 0)
                    AND _table_product_classaddress.attribute_id = '.(int)$classaddressAttrId,
                array('class_address'=>'_table_product_classaddress.value')        );
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $resource = Mage::getSingleton('core/resource');
        $itemTable = $resource->getTableName('sales_flat_order_item');

        $this->addColumn('real_order_id', array(
            'header'=> Mage::helper('sales')->__('编号'),
            'width' => '80',
            'type'  => 'text',
            'index' => 'product_id',
            'filter_index'=>$itemTable.'.product_id',
        ));


        $this->addColumn('name',array
        (
            'header'=>Mage::helper('sales')->__('课程名称'),
            'type'=>'text',
            'index'=>'name',
            'filter_index'=>$itemTable.'.name',
        ));

        $this->addColumn('class_date',array
        (
            'header'=>Mage::helper('sales')->__('上课日期'),
            'type'=>'date',
            'index'=>'classdate',
            'filter_index'=>'_table_product_classdate.value',
            'filter_condition_callback'=>array($this, '_classdateFilter'),
        ));
        $this->addColumn('n_clastime1',array
        (
            'header'=>Mage::helper('sales')->__('开始时间'),
            'type'=>'text',
            'index'=>'nclasstime1',
            'width'=>'50',
            'filter_index'=>'_table_product_nclasstime1.value',
        ));
        $this->addColumn('n_clastime2',array
        (
            'header'=>Mage::helper('sales')->__('结束时间'),
            'type'=>'text',
            'index'=>'nclasstime2',
            'width'=>'50',
            'filter_index'=>'_table_product_nclasstime2.value',
        ));

        $this->addColumn('qty_sum',array
        (
            'header'=>Mage::helper('sales')->__('上课人数'),
            'type'=>'number',
            'index'=>'qty_sum',
            'width'=>'50',
            'filter_index'=>'qty_sum',
            'filter_condition_callback'=>array($this, '_qtysumFilter'),
        ));
        
        $this->addColumn('qty',array
        (
            'header'=>Mage::helper('sales')->__('库存'),
            'type'=>'number',
            'index'=>'qty',
            'width'=>'50',
            'filter_index'=>'_table_product_stock.qty',
        ));



        $this->addColumn('seats',array
        (
            'header'=>Mage::helper('sales')->__('座位数'),
            'type'=>'number',
            'index'=>'seats',
            'width'=>'50',
            'filter_index'=>'_table_product_seats.value',
        ));
        
        $this->addColumn('order_count',array
        (
            'header'=>Mage::helper('sales')->__('订单数'),
            'type'=>'number',
            'index'=>'order_count',
            'width'=>'50',
            'filter_index'=>'order_count',
            'filter_condition_callback'=>array($this, '_ordercountFilter'),
        ));
        $this->addColumn('order_sum',array
        (
            'header'=>Mage::helper('sales')->__('付款金额'),
            'type'=>'number',
            'index'=>'order_sum',
            'width'=>'50',
            'filter_index'=>'order_sum',
            'filter_condition_callback'=>array($this, '_ordersumFilter'),
        ));

        $this->addColumn('credit_amount_sum',array
        (
            'header'=>Mage::helper('sales')->__('课点支付'),
            'type'=>'number',
            'index'=>'credit_amount_sum',
            'width'=>'50',
            'filter_index'=>'credit_amount_sum',
            //'sortable'=>false,

            'filter_condition_callback'=>array($this, '_creditamountsumFilter'),
        ));







        $model=Mage::getResourceModel('catalog/product_collection');
        $attr=$model->getAttribute('province');
        $optionss = $attr->getSource()->getAllOptions();
        //var_dump($options);
        // array(3) {[0]=> array(2) { ["label"]=> string(0) "" ["value"]=> string(0) "" } [1]=> array(2) { ["value"]=> string(2) "12" ["label"]=> string(6) "上海" } [2]=> array(2) { ["value"]=> string(2) "19" ["label"]=> string(6) "北京" } }
        $arrCityoptions=array();
        foreach($optionss as $item)
        {
            $label=$item['label'];
            $value=$item['value'];
            if ($value=="") continue;
            $arrCityoptions[$value]=$label;
        }
        $this->addColumn('province',array
        (
            'header'=>Mage::helper('sales')->__('城市'),
            'type'=>'options',
            'index'=>'province',
            'width' => '60',
            'filter_index'=>'_table_product_classprovince.value',
            'options' =>$arrCityoptions,

        ));
        $this->addColumn('class_address',array
        (
            'header'=>Mage::helper('sales')->__('上课地址'),
            'type'=>'text',
            'index'=>'class_address',
            'filter_index'=>'`_table_product_classaddress`.`value`',
        ));



        $this->addExportType('*/*/exportCsv/code/gbk', Mage::helper('sales')->__('CSV(中文)'));
        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV(utf-8)'));
        $this->addExportType('*/*/exportXml', Mage::helper('sales')->__('Excel XML'));


        return parent::_prepareColumns();
    }


    public function getRowUrl($row)
    {
        return false;
    }




    protected function _qtysumFilter($collection, $column)
    {
        // SUM(aca_sales_flat_order_item.qty_ordered) AS `item_count`,
        if(!$value = $column->getFilter()->getValue())
        {
            return $this;
        }
        $from = $value['from'];
        $to = $value['to'];
        if(!is_null($from))
        {
            $this->getCollection()->getSelect()->having('SUM(qty_ordered)>=?',$from);
        }
        if(!is_null($to))
        {
            $this->getCollection()->getSelect()->having('SUM(qty_ordered)<=?',$to);
        }
        return $this;
    }

    protected function _ordercountFilter($collection, $column)
    {
        if(!$value = $column->getFilter()->getValue())
        {
            return $this;
        }
        $from = $value['from'];
        $to = $value['to'];
        if(!is_null($from))
        {
            $this->getCollection()->getSelect()->having('count(*)>=?',$from);
        }
        if(!is_null($to))
        {
            $this->getCollection()->getSelect()->having('count(*)<=?',$to);
        }
        return $this;
    }
    protected function _ordersumFilter($collection, $column)
    {
        if(!$value = $column->getFilter()->getValue())
        {
            return $this;
        }
        $from = $value['from'];
        $to = $value['to'];
        if(!is_null($from))
        {
            $this->getCollection()->getSelect()->having('sum(main_table.`grand_total`)>=?',$from);
        }
        if(!is_null($to))
        {
            $this->getCollection()->getSelect()->having('sum(main_table.`grand_total`)<=?',$to);
        }
        return $this;
    }

    protected function _creditamountsumFilter($collection, $column)
    {
        if(!$value = $column->getFilter()->getValue())
        {
            return $this;
        }
        $from = $value['from'];
        $to = $value['to'];
        $resource = Mage::getSingleton('core/resource');
        $orderTable=$resource->getTableName('sales_flat_order');

        if(!is_null($from))
        {
            $this->getCollection()->getSelect()->having('sum(-'.$orderTable.'.credit_amount)>=?',$from);
        }
        if(!is_null($to))
        {
            $this->getCollection()->getSelect()->having('sum(-'.$orderTable.'.credit_amount)<=?',$to);
        }
        return $this;
    }

    protected function _classdateFilter($collection, $column)
    {
        if(!$value = $column->getFilter()->getValue())
        {
            return $this;
        }

        $from = $value['from'];
        $to = $value['to'];
        //2014-11-1
        //_table_product_classdate.value
        if(!is_null($from))
        {
            /* @var $from Zend_Date */
            $date=Mage::app()->getLocale()->date($from);
            $str= $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
            $this->getCollection()->getSelect()->where('_table_product_classdate.value >= ?',$str);
        }
        if(!is_null($to))
        {

            $date=Mage::app()->getLocale()->date($to);
            $str= $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
            $this->getCollection()->getSelect()->where('_table_product_classdate.value <= ?', $str);
        }
        //die($this->getCollection()->getSelectSql());

        return $this;
    }




    protected function _setCollectionOrder($column)
    {
        if ($column->getOrderCallback())
        {
            call_user_func($column->getOrderCallback(), $this->getCollection(), $column);

            return $this;
        }

        return parent::_setCollectionOrder($column);
    }





}