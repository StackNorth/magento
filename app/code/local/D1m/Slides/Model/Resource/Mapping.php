<?php
/**
 * Created by Victor Guo
 * Date: 13-8-16
 * Time: 下午3:27
 */
class D1m_Slides_Model_Resource_Mapping extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('d1m_slides/mapping', 'mapping_id');
    }
}