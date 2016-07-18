<?php

class Robi_Checkout_Block_Overview extends Mage_Core_Block_Template
{
	protected $_customer = null;
    protected $_checkout = null;
    protected $_quote    = null;
        
     public function __construct()
    {
        parent::__construct();
        
        $this->getQuote()->collectTotals()->save();
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
    
    
    /**
     * Get logged in customer
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        if (null === $this->_customer) {
            $this->_customer = Mage::getSingleton('customer/session')->getCustomer();
        }
        return $this->_customer;
    }

    /**
     * Get checkout session
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        if (null === $this->_checkout) {
            $this->_checkout = Mage::getSingleton('checkout/session');
        }
        return $this->_checkout;
    }

    /**
     * Get active quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if (null === $this->_quote) {
            $this->_quote = $this->getCheckout()->getQuote();
        }
        return $this->_quote;
    }
    
    public function getBillingAddress()
    {
    	
    	return $this->getQuote()->getBillingAddress();
    }
    
    public function getItems()
    {
        return Mage::getSingleton('checkout/session')->getQuote()->getAllVisibleItems();
    }

    public function getTotals()
    {
        return Mage::getSingleton('checkout/session')->getQuote()->getTotals();
    }
    

}