<?php
/**
 *  show account coupon list for current customer
 *
 *  @author      Song
 */
class D1m_CouponRule_Block_Customer_Coupon extends Mage_Core_Block_Template
{
    const  CUSTOMER_ACCOUNT_COUPON_LIST_PER_PAGE_SHOW_NUMBER = 20;

    public function __construct()
    {
        parent::__construct();
        //在控制中定义分页
       	$this->setCollection($this->getCouponList());
    }

    public function getCouponList()
    {
        if (!$this->hasData('coupon_list')) {
            $this->setData('coupon_list', Mage::registry('coupon_list'));
        }

        return $this->getData('coupon_list');
    }

    protected function _prepareLayout()
    {
            parent::_prepareLayout();

            if ($this->getCollection()){
                $pager = $this->getLayout()->createBlock('page/html_pager', 'custom.pager')
                ->setTemplate('coupon/page/html/pager.phtml');
                // $pager->setAvailableLimit(array(1=>1,10=>10,20=>20,'all'=>'all'));
                $pager->setAvailableLimit(array(D1m_CouponRule_Block_Customer_Coupon::CUSTOMER_ACCOUNT_COUPON_LIST_PER_PAGE_SHOW_NUMBER=>D1m_CouponRule_Block_Customer_Coupon::CUSTOMER_ACCOUNT_COUPON_LIST_PER_PAGE_SHOW_NUMBER));
                $pager->setCollection($this->getCollection());
                $this->setChild('pager', $pager);
                $this->getCollection()->load();
                return $this;
            }
    }

    public function getPagerHtml()
    {
            return $this->getChildHtml('pager');
    }

    public function hasAllUseForCoupon($coupon_per_use,$coupon_id,$ruleId){
        $customer_id = Mage::helper('couponRule')->getSession()->getCustomerId();
        $rule_coupon = Mage::getModel('salesrule/coupon')->load($coupon_id);

        if ($rule_coupon->getUsagePerCustomer()){
            $ruleCustomer          = Mage::getModel('salesrule/rule_customer')->loadByCustomerRule($customer_id, $ruleId);
            $rule_coupon_time_used = $ruleCustomer->getTimesUsed();
        }

        if (!empty($rule_coupon_time_used)) {
             return $rule_coupon_time_used;
        }
         return null;
     }

    /**
     *
     * @param type $couponExpireDate
     * @param type $RuleExpireDate
     */
    public function formatExpireDate($couponExpireDate,$ruleExpireDate){

         if (!empty($couponExpireDate) && !empty($ruleExpireDate)) {
             return Mage::app()->getLocale()->date(strtotime($couponExpireDate))->toString('yyyy-M-d');
         } elseif (!empty($couponExpireDate) && empty($ruleExpireDate)){
             return  Mage::app()->getLocale()->date(strtotime($couponExpireDate))->toString('yyyy-M-d');
         } elseif (empty($couponExpireDate) && !empty($ruleExpireDate)){
             return $ruleExpireDate;
         } else{
             return Mage::helper('couponRule')->__('-');
         }
    }




    /**
     * 判断是否是正常使用完
     * @param $coupon_per_use
     * @param $coupon_id
     * @param $ruleId
     */
    public function hasAllUse($coupon_per_use,$coupon_id,$ruleId){
        $rule_coupon_time_used = $this->hasAllUseForCoupon($coupon_per_use,$coupon_id,$ruleId);
        //如果存在单个人使用次数的限制
        if ($rule_coupon_time_used && $coupon_per_use){
            if ((int)$rule_coupon_time_used >= (int)$coupon_per_use) {
                return true;
            }
        }
   }

   public function formatUseTimeForcoupon($coupon_per_use,$coupon_id,$ruleId){

       $rule_coupon_time_used = $this->hasAllUseForCoupon($coupon_per_use,$coupon_id,$ruleId);
       //如果存在单个人使用次数的限制
       if ($rule_coupon_time_used && $coupon_per_use){
           if ((int)$rule_coupon_time_used >= (int)$coupon_per_use) {
                return   Mage::helper('couponRule')->__('未使用');
           } else {
                return   '<span class="has_times">'.Mage::helper('couponRule')->__("Your Can Use  %s Times",(int)$coupon_per_use - (int)$rule_coupon_time_used).'</span>';
           }
       }elseif(empty($rule_coupon_time_used) && $coupon_per_use){
             return   '<span class="has_times">'.Mage::helper('couponRule')->__('you dont use it,you can use %s Times',$coupon_per_use).'</span>';
       } else {
             return   '<span class="has_times">'.Mage::helper('couponRule')->__('no use times limit!').'</span>';
       }
   }
}
