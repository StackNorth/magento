<?php
class D1m_Course_Block_List extends Mage_Core_Block_Template
{
    

    public  $YearAndMonth = null;
    
    public $_collection = null;
    public $_collectionLoad = null;

    public function __construct()
    {
        parent::__construct();
    }
    

   
    /**
     * Add meta information from product to head block
     *
     * @return Mage_Catalog_Block_Product_View
     */
    protected function _prepareLayout()
    {
    	
    	$brand 	   = $this->getBrand();
        $headBlock = $this->getLayout()->getBlock('head');
        $template  = $this->getTemplateCode();
        
        if ($headBlock) {
             $headBlock->setTitle(Mage::helper('d1m_course')->__('Course'));
        }
        
        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            
            $breadcrumbsBlock->addCrumb('home', array(
                'label'=>Mage::helper('d1m_course')->__('Home'),
                'title'=>Mage::helper('d1m_course')->__('Go to Home Page'),
                'link'=>Mage::getBaseUrl()
            ));
			
			$breadcrumbsBlock->addCrumb('new_zone', array(
                'label'=>Mage::helper('d1m_course')->__('Course'),
                'title'=>Mage::helper('d1m_course')->__('Course'),
                'link'=>''
            ));
            
        }
        
         return parent::_prepareLayout();
    }
    
    public function getLastNum()
    {
        $collection = $this->getCollection();
        return $collection->getPageSize()*($collection->getCurPage()-1)+$collection->count();
    }
    
    public function getCollection()
    {
    	if(is_null($this->_collection))
        {
           $this->_collection = $this->getCollectionLoad();
           
        }
            $this->_collection->setCurPage($this->getCurrentPage());
    
            // we need to set pagination only if passed value integer and more that 0
            $limit = (int)$this->getLimit();
            if ($limit) {
                 $this->_collection->setPageSize($limit);
            }
            
            return $this->_collection;
    }
    
    public function getCollectionLoad()
    {
        	$year = $this->getYear();
            $month = $this->getMonth();
            
            $province = $this->getProvince();
            $coursetype = $this->getCoursetype();
            
            $fixeddate   = $this->getFixeddate();
            
            $_collection =  $this->getCourseCollection($year, $month, $province, $coursetype, $fixeddate);
            
            return $_collection;
    }
    
    public function getLimit()
    {
    	return 500;
    }
    
    public function getCurrentPage()
    {
    	 if ($page = (int) $this->getRequest()->getParam('p')) {
            return $page;
        }
        return 1;
    }

    public function getTotalNum()
    {
        return $this->getCollection()->getSize();
    }

    public function isFirstPage()
    {
        return $this->getCollection()->getCurPage() == 1;
    }

    public function getLastPageNum()
    {
        return $this->getCollection()->getLastPageNumber();
    }
    
    public function getUrlParams()
    {
    	return $this->getRequest()->getParams();
    }
    
    public function getYear()
    {
    	$yearAndMonth  = $this->iniYearAndMonth();
        return isset($yearAndMonth['year'])  ? $yearAndMonth['year'] : date('Y');
    }
    
    public function getMonth()
    {
    	$yearAndMonth  = $this->iniYearAndMonth();
        return isset($yearAndMonth['month'])  ? $yearAndMonth['month'] : date('m');
    }
    
    public function getMonthdate()
    {
    	return $this->getRequest()->getParam('monthdate', date('Y-m'));
    }
    
    public function getProvince()
    {
    	return $this->getRequest()->getParam('province', null);
    }
    
    public function getCoursetype()
    {
    	return $this->getRequest()->getParam('coursetype', null);
    }
    
    public function getFixeddate()
    {
    	return $this->getRequest()->getParam('fixeddate', null);
    }
    
    public function iniYearAndMonth()
    {
    	if(is_null($this->YearAndMonth))
        {
        	$tempstr = $this->getMonthdate();
            
            if($tempstr == '')
            {
            	$tempstr = date('Y-m');
            }
            
            $temps = explode('-', $tempstr);
            if(count($temps) != 2)
            {
            	$this->YearAndMonth['year'] = date('Y');
            	$this->YearAndMonth['month'] = date('m');
            }
            else
            {
            	$this->YearAndMonth['year'] = $temps[0];
                $this->YearAndMonth['month'] = $temps[1];
                
            }
        }
        
        return $this->YearAndMonth;
        
        
    }
    
    public function getRatingSummary($product_id){
    	$reviewSummary =  Mage::getModel('rating/rating')->getEntitySummary($product_id);
    	$sum = $reviewSummary->getData('sum');
    	$count = $reviewSummary->getData('count');
    	
    	if($count)
    	{
    		return floor(($sum/20)/$count);
    	}
    	else
    	{
    		return 0;
    	}
    }
    
    public function getALLMonthsSelects()
    {
    	return Mage::helper('d1m_course')->getALLMonthsSelects();
    }
    
    public function getAllCourseTypeSelects()
    {
    	return Mage::helper('d1m_course')->getAllCourseTypeSelects();
    }
    
    public function getAllProvinceSelects()
    {
    	return Mage::helper('d1m_course')->getAllProvinceSelects();
    }
    
    public function getCourseCollection($year, $month, $province, $coursetype, $fixeddate = null)
    {
    	$days = cal_days_in_month(CAL_GREGORIAN,$month, $year);
        
        $hasFixeddate = false;
        
        if($fixeddate)
        {
            $fixedates = explode('-', $fixeddate);
            
            if(count($fixedates) == 3)
            {
            	
                if(checkdate($fixedates[1], $fixedates[2], $fixedates[0]))
                {
                	$fromDate = date('Y-m-d H:i:s', mktime(0, 0, 0, $fixedates[1], $fixedates[2], $fixedates[0]));
                    $toDate = date('Y-m-d H:i:s', mktime(23, 59, 59, $fixedates[1], $fixedates[2], $fixedates[0]));
                    $hasFixeddate = true;
                }
                
            }
        	
        }
        
        if(!$hasFixeddate)
        {
        	$fromDate = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
            $toDate = date('Y-m-d', mktime(0, 0, 0, $month, $days, $year));
        }
        
        $courses =  Mage::getResourceModel('d1m_course/course_collection')
                        ->addAttributeToSelect('coursetype')
                        ->addAttributeToSelect('province')
                        ->addAttributeToSelect('class_date')
                        ->addAttributeToSelect('n_classtime1')
                        ->addAttributeToSelect('n_classtime2')
                        ->addUrlRewrite()
                        //->addStoreFilter()
                        ;
         
         $courses->addFieldToFilter('class_date', array("gteq"=>$fromDate));
         $courses->addFieldToFilter('class_date', array("lteq"=>$toDate));
         
         if($province)
         {
            $courses->addFieldToFilter('province', $province);
         }
         
         $n_shop = $this->getRequest()->getParam('n_shop');
         if($n_shop){
             $courses->addFieldToFilter('n_shop', $n_shop);
         }
         
         if($coursetype)
         {
            $courses->addFieldToFilter('coursetype', array('like'=>'%'.$coursetype.'%'));
         }
         
         $courses->setOrder('class_date','asc');
         $courses->setOrder('n_classtime1','asc');

         //Mage::getModel('catalogInventory/stock_status')->addStockStatusToProducts($courses);
         
         //echo $courses->getSelect();
         
         return $courses;
    }
    
    public function getCourseData($year, $month, $province, $coursetype)
    {
        $data = array();
        
    	$courses =  $this->getCourseCollection($year, $month, $province, $coursetype);
         
         foreach($courses as $course)
         {
            $coursetype = $course->getAttributeText('coursetype');
         	$day = date('j', strtotime($course->class_date));

            
            $isStock = $course->getStockItem()->getIsInStock();
            
            if($isStock)
            {
            	$stockCls = ' hasstock';
            }
            else
            {
            	$stockCls = 'nostock';
            }
            

            
         }
         
         return $data;
        
    }
    
    public function getCourseFullData($courseId)
    {
    	$course = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($courseId);
        return $course;
    }
    
    public function getCourseSchedule()
    {
        
        $_collection = $this->getCollection();
 
        $_collection->load();
        
        Mage::getModel('catalogInventory/stock_status')->addStockStatusToProducts($_collection);
        
        
        return $_collection;
    }
    
    public function getCalendar()
    {
        $year = $this->getYear();
        $month = $this->getMonth();
        
        $province = $this->getProvince();
        $coursetype = $this->getCoursetype();
        
        $data = $this->getCourseData($year, $month, $province, $coursetype);
        
    	return Mage::helper('d1m_course/calendar')->generate($year, $month, $data);
        
    }
    
}