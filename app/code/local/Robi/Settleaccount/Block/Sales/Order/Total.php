<?php
class Robi_Settleaccount_Block_Sales_Order_Total extends Mage_Core_Block_Template
{
    /**
     * Get label cell tag properties
     *
     * @return string
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * Get order store object
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * Get totals source object
     *
     * @return Mage_Sales_Model_Order
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * Get value cell tag properties
     *
     * @return string
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * Initialize reward points totals
     *
     * @return Enterprise_Reward_Block_Sales_Order_Total
     */
    public function initTotals()
    {
// die('i am here');
        if ((float) $this->getOrder()->getBaseCreditAmount())
        {
            $source = $this->getSource();
            $value  = $source->getCreditAmount();

            $this->getParentBlock()->addTotal(new Varien_Object(array(
                'code'   => 'credit',
                'strong' => false,
                'label'  => '使用课点'.$source->getCreditQty(),
                'value'  => $source instanceof Mage_Sales_Model_Order_Creditmemo ? - $value : $value
            )));
        }
        if ((float) $this->getOrder()->getBaseRewardpointsAmount())
        {
            $source = $this->getSource();
            $value  = $source->getRewardpointsAmount();

            $this->getParentBlock()->addTotal(new Varien_Object(array(
                'code'   => 'rewardpoints',
                'strong' => false,
                'label'  => '积分抵款',
                'value'  => $source instanceof Mage_Sales_Model_Order_Creditmemo ? - $value : $value
            )));
        }



        return $this;
    }
}
