<?php

class Robi_Settleaccount_Model_Invoice_Total_Rewardpoints extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {

        $order = $invoice->getOrder();
        $rewardpointsAmountLeft = $order->getRewardpointsAmount() - $order->getRewardpointsAmountInvoiced();
        $baseRewardpointsAmountLeft = $order->getBaseRewardpointsAmount() - $order->getBaseRewardpointsAmountInvoiced();
        if (abs($baseRewardpointsAmountLeft) < $invoice->getBaseGrandTotal())
        {
            $invoice->setGrandTotal($invoice->getGrandTotal() + $rewardpointsAmountLeft);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseRewardpointsAmountLeft);
        }
        else
        {
            $rewardpointsAmountLeft = $invoice->getGrandTotal() * -1;
            $baseRewardpointsAmountLeft = $invoice->getBaseGrandTotal() * -1;

            $invoice->setGrandTotal(0);
            $invoice->setBaseGrandTotal(0);
        }

        $invoice->setRewardpointsAmount($rewardpointsAmountLeft);
        $invoice->setBaseRewardpointsAmount($baseRewardpointsAmountLeft);
        return $this;
        
        
        /*
        $invoice->setRewardpointsAmount(0);
        $invoice->setBaseRewardpointsAmount(0);
        
        $orderRewardpointsAmount        = $invoice->getOrder()->getRewardpointsAmount();
        $baseOrderRewardpointsAmount    = $invoice->getOrder()->getBaseRewardpointsAmount();

        if ($orderRewardpointsAmount) {
           
            $invoice->setRewardpointsAmount($orderRewardpointsAmount);
            $invoice->setBaseRewardpointsAmount($baseOrderRewardpointsAmount);

            $invoice->setGrandTotal($invoice->getGrandTotal() + $orderRewardpointsAmount);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseOrderRewardpointsAmount);
        }
        return $this;
        */
    }
}
