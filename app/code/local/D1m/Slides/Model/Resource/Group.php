<?php
/**
 * Created by Victor Guo
 * Date: 13-8-15
 * Time: 下午1:42
 */
class D1m_Slides_Model_Resource_Group extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('d1m_slides/group', 'group_id');
    }
}