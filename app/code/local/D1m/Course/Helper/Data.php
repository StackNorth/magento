<?php
class D1m_Course_Helper_Data extends Mage_Core_Helper_Abstract
{	

	public function getALLMonthsSelects()
    {
    	
        $month = date('m');
        $year = date('Y');
        
        $monthDatas = array();
        
        $monthDatas[] = array('value'=>date('Y-m', mktime(0,0,0,$month+1,0,$year)), 'label'=>date('Y-m', mktime(0,0,0,$month+1,0,$year)));
        $monthDatas[] = array('value'=>date('Y-m', mktime(0,0,0,$month+2,0,$year)), 'label'=>date('Y-m', mktime(0,0,0,$month+2,0,$year)));
        $monthDatas[] = array('value'=>date('Y-m', mktime(0,0,0,$month+3,0,$year)), 'label'=>date('Y-m', mktime(0,0,0,$month+3,0,$year)));
        $monthDatas[] = array('value'=>date('Y-m', mktime(0,0,0,$month+4,0,$year)), 'label'=>date('Y-m', mktime(0,0,0,$month+4,0,$year)));
        $monthDatas[] = array('value'=>date('Y-m', mktime(0,0,0,$month+5,0,$year)), 'label'=>date('Y-m', mktime(0,0,0,$month+5,0,$year)));
        
        return $monthDatas;
    }
    
    public function getAllCourseTypeSelects()
    {
    	return Mage::getResourceSingleton('catalog/product')->getAttribute('coursetype')->getSource()->getAllOptions();
    }
    
    public function getAllProvinceSelects()
    {
    	return Mage::getResourceSingleton('catalog/product')->getAttribute('province')->getSource()->getAllOptions();
    }
    
}
