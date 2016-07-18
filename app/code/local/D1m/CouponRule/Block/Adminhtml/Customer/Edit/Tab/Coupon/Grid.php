<?php

class D1m_CouponRule_Block_Adminhtml_Customer_Edit_Tab_Coupon_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('couponGrid');
        $this->setDefaultSort('coupon_id');
        $this->setDefaultDir('desc');

        $this->setUseAjax(true);

        $this->setEmptyText(Mage::helper('customer')->__('No coupon Found'));

    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/coupon', array('_current'=>true));
    }

    protected function _prepareCollection()
    {
       $customer_data = Mage::registry('current_customer')->getData();
       $collection = Mage::getResourceModel('salesrule/coupon_collection')
           // ->addRuleToFilter($priceRule)
		    ->addFieldToFilter('lk.customer_id', $customer_data['entity_id'])
            ->addFieldToFilter('main_table.is_primary', array('null' => 1));

        $collection->getSelect()->join(
                      array('lk'=>Mage::getSingleton('core/resource')->getTableName('couponRule/coupon')),
                     'lk.coupon = main_table.code',
                     array('customer_email'=>'lk.customer_email')
                      );
        $collection->getSelect()->group('main_table.code');

          $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('code', array(
            'header' => Mage::helper('customer')->__('Coupon Code'),
            'index'  => 'code',
            'width'  => '350'
        ));

        $this->addColumn('customer_rule_id', array(
            'header' => Mage::helper('customer')->__('Rule Name'),
            'index'  => '`lk`.`rule_id`',
            'align'  => 'left',
           'renderer' => 'couponRule/adminhtml_customer_edit_tab_coupon_grid_column_renderer_ruleName',
            //添加自定义的filter
           //  'renderer' => 'Mage_Adminhtml_Block_Customer_Edit_Tab_Coupon_Grid_Column_Renderer_RuleName',
            'width'  => '200'
        ));

       $this->addColumn('usage_limit', array(
            'header' => Mage::helper('customer')->__('usage limit'),
            'index'  => 'usage_limit',
            'align'  => 'left',
            'width'  => '200',
           'type'   => 'number'
        ));
       
        $this->addColumn('from_date', array(
            'header' => Mage::helper('customer')->__('Active Date From'),
            'index'  => 'from_date',
            'filter'    => false,
            'sortable'  =>false,
          //  'renderer' => 'Mage_Adminhtml_Block_Customer_Edit_Tab_Coupon_Grid_Column_Renderer_RuleActiveDate',
            'width'  => '200'
        ));

        $this->addColumn('expiration_date', array(
            'header' => Mage::helper('customer')->__('expiration date'),
            'index'  => 'expiration_date',
            'filter'    => false,
            'sortable'  =>false,
            'renderer' => 'couponRule/adminhtml_customer_edit_tab_coupon_grid_column_renderer_ruleExpireDate',
          //  'renderer' => 'Mage_Adminhtml_Block_Customer_Edit_Tab_Coupon_Grid_Column_Renderer_RuleExpireDate',
            'width'  => '200'
        ));
        
       $this->addColumn('times_used', array(
            'header' => Mage::helper('customer')->__('time used'),
            'index'  => 'times_used',
            'width'  => '200',
           'type'   => 'number'
        ));

        return parent::_prepareColumns();
    }
}
