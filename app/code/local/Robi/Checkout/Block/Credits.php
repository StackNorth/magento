<?php
class Robi_Checkout_Block_Credits extends Mage_Core_Block_Template
{

    public function __construct()
    {
        $template = $this->getTemplateCode();
        $templateFile = 'credits/'.$template.'/main.phtml';
        $this->setTemplate($templateFile);
        parent::__construct();
    }
    
   public function getTemplateCode()
    {
    	$brand 	  = $this->getBrand();
        return Mage::helper('uemall_credits')->getCurrentTemplate($brand->getTemplate());
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
           $title = $brand->getPageTitle();
            if ($title) {
                $headBlock->setTitle($title);
            }
            $keyword = $brand->getMetaKeywords();
            if ($keyword) {
                $headBlock->setKeywords($keyword);
            } 
            $description = $brand->getMetaDescription();
            if ($description) {
                $headBlock->setDescription($description);
            } 
            
            $templateMaterials = Mage::helper('uemall_brands')->getCurrentMaterials($template);
            $jsMaterials       = isset($templateMaterials['js']) ? $templateMaterials['js'] : array();
            foreach($jsMaterials as $jsPath)
            {
            	$headBlock->addJs('brand/'.$jsPath);
            }
            $cssMaterials 		= isset($templateMaterials['css']) ? $templateMaterials['css'] : array();
            foreach($cssMaterials as $cssPath)
            {
            	$headBlock->addCss($cssPath);
            }
            
        }
        
        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            
            $breadcrumbsBlock->addCrumb('home', array(
                'label'=>Mage::helper('catalog')->__('Home'),
                'title'=>Mage::helper('catalog')->__('Go to Home Page'),
                'link'=>Mage::getBaseUrl()
            ));
			
			$breadcrumbsBlock->addCrumb('brand_zone', array(
                'label'=>'品牌专柜',
                'title'=>'品牌专柜',
                'link'=>'/brand/dolce-gabbana.html'
            ));
            
            $breadcrumbsBlock->addCrumb('brand', array(
                'label'=> $brand->getBrandZh().' '.$brand->getBrandEn(),
                'title'=> $brand->getBrandZh().' '.$brand->getBrandEn(),
                'link'=>''
            ));
			
        }
        
         return parent::_prepareLayout();
    }
    
    public function getProductListHtml()
    {
        return $this->getChildHtml('product_list');
    }
    
    
    public function getUrlParams()
    {
    	return $this->getRequest()->getParams();
    }
    
    public function getLayoutType()
    {
    	 $data = $this->getRequest()->getParam('layout','big');
    	 return ($data != 'small') ? 'big' : $data;
    }
    
    
    public function getBrand()
    {
    	//current_event
    	return Mage::registry('current_credit');
    }
    
    
}