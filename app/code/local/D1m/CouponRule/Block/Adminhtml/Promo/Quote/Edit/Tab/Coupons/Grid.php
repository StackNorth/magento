<?php
class  D1m_CouponRule_Block_Adminhtml_Promo_Quote_Edit_Tab_Coupons_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('couponCodesGrid');
        $this->setUseAjax(true);
        $this->setFilterVisibility(true);
        $this->setPagerVisibility(true);
    }




    /**
     * Prepare collection for grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $priceRule = Mage::registry('current_promo_quote_rule');

        /**
         * @var Mage_SalesRule_Model_Resource_Coupon_Collection $collection
         */
        $collection = Mage::getResourceModel('salesrule/coupon_collection')
            //->addRuleToFilter($priceRule)
            ->addFieldToFilter('is_primary', array('null' => 1));
        if ($priceRule instanceof Mage_SalesRule_Model_Rule) {
            $ruleId = $priceRule->getId();
        } else {
            $ruleId = (int)$priceRule;
        }

        //如果RULE与用户绑定，则显示客户邮箱
        if ($priceRule->getCouponType() == D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_WITH_CUSTOMER || $priceRule->getCouponType() == D1m_CouponRule_Model_Rule::COUPON_TYPE_CREDITS_AUTO_GENERATE_WITH_CUSTOMER ){
            $collection->getSelect()->join(
                array('lk'=>Mage::getSingleton('core/resource')->getTableName('couponRule/coupon')),
                'lk.coupon = main_table.code',
                array('customer_email'=>'lk.customer_email')
            )
                ->group('main_table.code');
        }

        //如果RULE是与EVENT绑定
        if ($priceRule->getCouponType() == D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_FOR_EVENT){
            $collection->getSelect()->join(
                array('lk'=>Mage::getSingleton('core/resource')->getTableName('couponRule/coupon')),
                'lk.coupon = main_table.code',
                array('customer_email'=>'lk.customer_email')
            )
                ->group('main_table.code');
        }

        $collection->addFieldToFilter('main_table.rule_id', $ruleId);
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Define grid columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $priceRule = Mage::registry('current_promo_quote_rule');

        $this->addColumn('code', array(
            'header' => Mage::helper('salesrule')->__('Coupon Code'),
            'index'  => 'code'
        ));

        if ($priceRule->getCouponType() == D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_WITH_CUSTOMER  || $priceRule->getCouponType() == D1m_CouponRule_Model_Rule::COUPON_TYPE_CREDITS_AUTO_GENERATE_WITH_CUSTOMER){
            $this->addColumn('customer_email', array(
                'header' => Mage::helper('couponRule/data')->__('Customer Email'),
                'index'  => 'customer_email',
                'align'  => 'left',
                'width'  => '150'
            ));
        }

        //如果为自动事件，则列出有效期
        if ($priceRule->getCouponType() == D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_FOR_EVENT){

            $this->addColumn('from_date', array(
                'header' => Mage::helper('salesrule')->__('Coupon From Date'),
                'index'  => 'from_date',
                'align'  => 'left',
                'gmtoffset' => true,
                'format'    => 'Y-M-d',
                'type' 		=>'datetime',
                'width'  => '150'
            ));

            $this->addColumn('expiration_date', array(
                'header' => Mage::helper('salesrule')->__('Coupon Expiration Date'),
                'index'  => 'expiration_date',
                'align'  => 'left',
                'gmtoffset' => true,
                'format'    => 'Y-M-d',
                'type'      =>'datetime',
                'width'  => '150'
            ));
        }


        $this->addColumn('created_at', array(
            'header' => Mage::helper('salesrule')->__('Created On'),
            'index'  => 'created_at',
            'type'   => 'datetime',
            'align'  => 'center',
            'width'  => '160'
        ));

        $this->addColumn('used', array(
            'header'   => Mage::helper('salesrule')->__('Used'),
            'index'    => 'times_used',
            'width'    => '100',
            'type'     => 'options',
            'options'  => array(
                Mage::helper('adminhtml')->__('No'),
                Mage::helper('adminhtml')->__('Yes')
            ),
            'renderer' => 'adminhtml/promo_quote_edit_tab_coupons_grid_column_renderer_used',
            'filter_condition_callback' => array(
                Mage::getResourceModel('salesrule/coupon_collection'), 'addIsUsedFilterCallback'
            )
        ));

        $this->addColumn('times_used', array(
            'header' => Mage::helper('salesrule')->__('Times Used'),
            'index'  => 'times_used',
            'width'  => '50',
            'type'   => 'number',
        ));

        $this->addExportType('*/*/exportCouponsCsv', Mage::helper('customer')->__('CSV'));
        $this->addExportType('*/*/exportCouponsXml', Mage::helper('customer')->__('Excel XML'));
        return parent::_prepareColumns();
    }

    /**
     * Configure grid mass actions
     *
     * @return Mage_Adminhtml_Block_Promo_Quote_Edit_Tab_Coupons_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('coupon_id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        $this->getMassactionBlock()->setUseAjax(true);
        $this->getMassactionBlock()->setHideFormElement(true);

        $this->getMassactionBlock()->addItem('delete', array(
            'label'=> Mage::helper('adminhtml')->__('Delete'),
            'url'  => $this->getUrl('*/*/couponsMassDelete', array('_current' => true)),
            'confirm' => Mage::helper('salesrule')->__('Are you sure you want to delete the selected coupon(s)?'),
            'complete' => 'refreshCouponCodesGrid'
        ));

        return $this;
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/couponsGrid', array('_current'=> true));
    }
}
