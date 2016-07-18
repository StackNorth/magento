<?php
/**
 * Created by Victor Guo
 * Date: 13-8-15
 * Time: 下午1:39
 */
class D1m_Slides_Model_Group extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('d1m_slides/group');
    }
}