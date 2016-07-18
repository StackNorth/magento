<?php
class D1m_Integral_Helper_Data extends Mage_Core_Helper_Abstract
{	
	public function getCreditAmountByCustomerId($customer_id)
    {
    	return Mage::getModel('d1m_integral/integral')->getCustomerCredits($customer_id);
    }

    public  function getUserPoints($phone){
       // return 0;
        if($phone) {
            $customerApi = Mage::getModel('customapi/memberservice');
            $customer = array();
            $customer['mobile'] = $phone;//'13945544444';

            $erpCustomer = $customerApi->searchMembers($customer, 0);

            if($erpCustomer){
                return $erpCustomer->ValidPointsClass + 0;
            }
            return 0;
        }
        return 0;
    }
}
