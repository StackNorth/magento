<?php

class D1m_Customer_Model_Entity_Customer extends Mage_Customer_Model_Entity_Customer
{
    
    
    /**
     * Load customer by account ID
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param string $accountId
     * @param bool $testOnly
     * @return Mage_Customer_Model_Entity_Customer
     * @throws Mage_Core_Exception
     */
    public function loadByAccountId(Mage_Customer_Model_Customer $customer, $accountId, $testOnly = false)
    {
        $acctAttr = $this->getAttribute("account_number");
        $select = $this->_getReadAdapter()->select()
            ->from($this->getEntityTable(), array($this->getEntityIdField()))
            ->join($acctAttr->getBackendTable(),$acctAttr->getBackendTable().".entity_id=".$this->getEntityTable().".entity_id")
            //->where('email=?', $email);
            ->where('attribute_id=?', $acctAttr->getAttributeId())
            ->where('value=?', $accountId);

        if ($customer->getSharingConfig()->isWebsiteScope()) {
            if (!$customer->hasData('website_id')) {
                Mage::throwException(Mage::helper('customer')->__('Customer website ID must be specified when using the website scope.'));
            }
            $select->where('website_id=?', (int)$customer->getWebsiteId());
        }

        $id = $this->_getReadAdapter()->fetchOne($select);
        if ($id) {
            $this->load($customer, $id);
        }
        else {
            $customer->setData(array());
        }
        return $this;
    }
}

