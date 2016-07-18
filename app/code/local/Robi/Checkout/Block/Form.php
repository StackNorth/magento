<?php

require_once "Mage/Checkout/Block/Cart.php";

class Robi_Checkout_Block_Form extends Mage_Checkout_Block_Cart
{
        
     public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Get shopping cart items qty based on configuration (summary qty or items qty)
     *
     * @return int | float
     */
    public function getSummaryCount()
    {
        if ($this->getData('summary_qty')) {
            return $this->getData('summary_qty');
        }
        return Mage::getSingleton('checkout/cart')->getSummaryQty();
    }
    
    /**
     * Get shopping cart subtotal.
     *
     * It will include tax, if required by config settings.
     *
     * @param   bool $skipTax flag for getting price with tax or not. Ignored in case when we display just subtotal incl.tax
     * @return  decimal
     */
    public function getSubtotal($skipTax = true)
    {
        $subtotal = 0;
        $totals = $this->getTotals();
        $config = Mage::getSingleton('tax/config');
        if (isset($totals['subtotal'])) {
            if ($config->displayCartSubtotalBoth()) {
                if ($skipTax) {
                    $subtotal = $totals['subtotal']->getValueExclTax();
                } else {
                    $subtotal = $totals['subtotal']->getValueInclTax();
                }
            } elseif($config->displayCartSubtotalInclTax()) {
                $subtotal = $totals['subtotal']->getValueInclTax();
            } else {
                $subtotal = $totals['subtotal']->getValue();
                if (!$skipTax && isset($totals['tax'])) {
                    $subtotal+= $totals['tax']->getValue();
                }
            }
        }
        return $subtotal;
    }
    
    public function getGrandtotal($skipTax = true)
    {
        $grandtotal = 0;
        $totals = $this->getTotals();
        if (isset($totals['grand_total'])) {
           $grandtotal = $totals['grand_total']->getValue();
        }
        return $grandtotal;
    }
    
    public function getCouponCode()
    {
        return $this->getQuote()->getCouponCode();
    }
    
        /**
     * Add meta information from product to head block
     *
     * @return Mage_Catalog_Block_Product_View
     */
    protected function _prepareLayout()
    {
        
        $headBlock = $this->getLayout()->getBlock('head');
      
         return parent::_prepareLayout();
    }
    
    
    public function getFeeByCredit()
    {
    	
        return Mage::helper('d1m_credits')->getFeeByCredit();
        
    }
    
    public function getUserRewardpointAmount()
    {
    	return Mage::helper('settleaccount/points')->getCurrentPoints();
    }
    
    public function getUsedCreditsAmount()
    {
    	return Mage::helper('settleaccount/event')->getPrepareUsedCredit();
    }
        
    public function getUsedRewardpointsAmount()
    {
    	return Mage::helper('settleaccount')->convertMoneyToPoints($this->getQuote()->getUsedRewardpointsAmount());
    }
        
    
    public function getCustomer()
    {
    	return Mage::getSingleton('customer/session')->getCustomer();
    }


    public function getCreditsByCustomer()
    {
    	
        $customer = $this->getCustomer();
        return Mage::helper('d1m_credits')->getCreditAmountByCustomerId($customer->getId());
        
    }
}