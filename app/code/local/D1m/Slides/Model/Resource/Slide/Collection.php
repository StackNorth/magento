<?php
/**
 * Created by Victor Guo
 * Date: 13-8-15
 * Time: 下午1:45
 */
class D1m_Slides_Model_Resource_Slide_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('d1m_slides/slide');
    }
}