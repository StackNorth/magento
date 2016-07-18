<?php

class Robi_Settleaccount_Model_Creditmemo_Total_Credit extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {

        $order = $creditmemo->getOrder();
//        mage::log('order creditamount:'.$order->getCreditAmount());
        $feeAmountLeft = $order->getCreditAmount() - $order->getCreditAmountRefunded();
        $basefeeAmountLeft = $order->getBaseCreditAmount() - $order->getBaseCreditAmountRefunded();
       // if ($basefeeAmountLeft!=0) //-279
        {
  //          mage::log('fee amount left:'.$feeAmountLeft);
 //           mage::log('base fee amount left:'.$basefeeAmountLeft);

     //       mage::log('gand total:'.$creditmemo->getGrandTotal() );
   //         mage::log('base gand total:'.$creditmemo->getBaseGrandTotal() );

            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $feeAmountLeft);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $basefeeAmountLeft);

            $creditmemo->setCreditAmount($feeAmountLeft);
            $creditmemo->setBaseCreditAmount($basefeeAmountLeft);
        }
        return $this;

    }


    



    
}
