<?php

class D1m_Customer_Model_Resource_Customer extends Mage_Customer_Model_Resource_Customer
{
    
    /**
     * Check customer scope, email and confirmation key before saving
     *
     * @param Mage_Customer_Model_Customer $customer
     * @throws Mage_Customer_Exception
     * @return Mage_Customer_Model_Resource_Customer
     */
    protected function _beforeSave(Varien_Object $customer)
    {
        parent::_beforeSave($customer);

        if (!$customer->getEmail()) {
            throw Mage::exception('Mage_Customer', Mage::helper('customer')->__('Customer email is required'));
        }

        $adapter = $this->_getWriteAdapter();
        $bind    = array('email' => $customer->getEmail());

        $select = $adapter->select()
            ->from($this->getEntityTable(), array($this->getEntityIdField()))
            ->where('email = :email');
        if ($customer->getSharingConfig()->isWebsiteScope()) {
            $bind['website_id'] = (int)$customer->getWebsiteId();
            $select->where('website_id = :website_id');
        }
        if ($customer->getId()) {
            $bind['entity_id'] = (int)$customer->getId();
            $select->where('entity_id != :entity_id');
        }

        $result = $adapter->fetchOne($select, $bind);
        if ($result) {
            throw Mage::exception(
                'Mage_Customer', Mage::helper('customer')->__('This customer email already exists'),
                Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS
            );
        }
        
        //added by robin at 2014/7/12
        //username
        $write = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $write->select()->from(Mage::getSingleton('core/resource')->getTableName('customer_entity_varchar'))
            ->where('value=?', $customer->getUsername())
            ->where('attribute_id=?', 144);
        if ($customer->getId()) {
                    $select->where('entity_id !=?', $customer->getId());
                }

        if ($write->fetchRow($select)) {
            throw Mage::exception('Mage_Core', Mage::helper('customer')->__('This username already exists.'),
                D1m_Customer_Model_Customer::EXCEPTION_USERNAME_EXISTS
            );
        }
        
        //phone
        $write = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $write->select()->from(Mage::getSingleton('core/resource')->getTableName('customer_entity_varchar'))
            ->where('value=?', $customer->getPhone())
            ->where('attribute_id=?', 145);
        if ($customer->getId()) {
                    $select->where('entity_id !=?', $customer->getId());
                }

        if ($write->fetchRow($select)) {
            throw Mage::exception('Mage_Core', Mage::helper('customer')->__('This phone already exists.'),
                D1m_Customer_Model_Customer::EXCEPTION_PHONE_EXISTS
            );
        }

        // set confirmation key logic
        if ($customer->getForceConfirmed()) {
            $customer->setConfirmation(null);
        } elseif (!$customer->getId() && $customer->isConfirmationRequired()) {
            $customer->setConfirmation($customer->getRandomConfirmationKey());
        }
        // remove customer confirmation key from database, if empty
        if (!$customer->getConfirmation()) {
            $customer->setConfirmation(null);
        }

        return $this;
    }
    
    
    
    
    
    
    
    
}