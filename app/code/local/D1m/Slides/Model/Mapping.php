<?php
/**
 * Created by Victor Guo
 * Date: 13-8-16
 * Time: 下午3:27
 */
class D1m_Slides_Model_Mapping extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('d1m_slides/mapping');
    }
}