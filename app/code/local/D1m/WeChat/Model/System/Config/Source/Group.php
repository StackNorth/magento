<?php
/***
 * Class D1m_WeChat_Model_System_Config_Source_Group
 */
class D1m_WeChat_Model_System_Config_Source_Group
{
    public function toOptionArray()
    {
        $customerGroup = array();

        /* @var $groups  Mage_Customer_Model_Resource_Customer_Collection  */
        $groups = Mage::getResourceModel('customer/group_collection')
            ->addFieldToFilter('customer_group_id', array('gt'=> 0))
            ->load();

        /* @var $group  Mage_Customer_Model_Group  */
        $customerGroup[] = array('value' => '', 'label' => '请选择');
        foreach($groups as $group)
        {
            $customerGroup[] =  array('value' => $group->getId(), 'label' =>$group->getCustomerGroupCode());
        }

        return $customerGroup;
    }

    public function toArray()
    {
        $customerGroup = array();

        /* @var $groups  Mage_Customer_Model_Resource_Customer_Collection  */
        $groups = Mage::getResourceModel('customer/group_collection')
            ->addFieldToFilter('customer_group_id', array('gt'=> 0))
            ->load();

        /* @var $group  Mage_Customer_Model_Group  */
        foreach($groups as $group)
        {
            $customerGroup[$group->getId()] =  $group->getCustomerGroupCode();
        }

        return $customerGroup;
    }
}
