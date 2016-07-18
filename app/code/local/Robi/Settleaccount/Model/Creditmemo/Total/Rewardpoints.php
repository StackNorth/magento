<?php
class Robi_Settleaccount_Model_Creditmemo_Total_Rewardpoints extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {

        $order = $creditmemo->getOrder();
        $feeAmountLeft = $order->getRewardpointsAmount() - $order->getRewardpointsAmountRefunded();
        $basefeeAmountLeft = $order->getBaseRewardpointsAmount() - $order->getBaseRewardpointsAmountRefunded();
        // if ($basefeeAmountLeft!=0) //-279
        {
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $feeAmountLeft);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $basefeeAmountLeft);
            $creditmemo->setRewardpointsAmount($feeAmountLeft);
            $creditmemo->setBaseCreditAmount($basefeeAmountLeft);
        }
        return $this;


    }
}
