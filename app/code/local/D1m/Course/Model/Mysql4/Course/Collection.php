<?php

class D1m_Course_Model_Mysql4_Course_Collection
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
{
    public function __construct($resource=null)
    {
        parent::__construct();
        $this->_construct();
        $this->setConnection($this->getEntity()->getWriteConnection());
        $this->_prepareStaticFields();
        $this->_initSelect();
    }
    

}
