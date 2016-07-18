<?php

class D1m_Credits_Block_Test extends Mage_Core_Block_Template
{

    public function __construct()
    {
        parent::__construct();

    }
    public function getReadConnection(){
        return  Mage::getSingleton('core/resource')->getConnection('core_read');
    }
    public function findAllByOne($condition,$table)
    {
        return "select $condition from  $table";
    }
    public function findAll($table)
    {

        return "select * from  $table";
    }

    public function getArticleType()
    {
        return $this->getReadConnection()->fetchAll($this->findAllByOne('type','aca_credits_test_type'));
    }

    public function getArticleTitle()
    {
        return $this->getReadConnection()->fetchAll($this->findAllByOne('title','aca_credits_test'));
    }
    public function getArticleContentByTitle(){
        $sql = "select * from aca_credits_test where id = (select Max(id) from aca_credits_test );";
        return $this->getReadConnection()->fetchAll($sql);
    }
}
