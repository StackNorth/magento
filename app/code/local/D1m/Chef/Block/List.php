<?php
class D1m_Chef_Block_List extends Mage_Core_Block_Template {

    /**
     * chef list
     */
    public function _construct() 
    {
        $collection = $this->_getCollection();
        $this->setCollection($collection);
    }

    /**
     * Chef chefs source entrance.
     */
    protected function _getCollection() 
    {
        if ( !$this->hasData('current_chef_chefs') ) {
            if ( Mage::registry('current_chef_chefs') ) {
                $this->setData('current_chef_chefs', Mage::registry('current_chef_chefs'));
            }else{
                $collection = Mage::getModel('d1m_chef/chef')->getCollection()
                    ->setOrder('corder', 'ASC');
                $this->setData('current_chef_chefs', $collection);
            }
        }
        return $this->getData('current_chef_chefs');
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->initBreadcrumb();
        $pager = $this->getLayout()->createBlock('page/html_pager', 'chef.list.pager')
            ->setTemplate('page/html/pagercommon.phtml');
        $pager->setAvailableLimit(array(5=>5, 10=>10,'all'=>'all'));
        $pager->setCollection($this->getCollection());
        $this->setChild('pager', $pager);
        $this->getCollection()->load();
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * get chef detail url
    */
    public function getChefDetailUrl($id)
    {
        return Mage::helper('d1m_chef')->getChefDetailUrl($id);
    }


    /**
     *  cut string
     */
    public function getCutString($string,$num,$flag = '...')
    {
        return Mage::helper('core/string')->truncate($string,$num,$flag);
    }


    protected function initBreadcrumb()
    {
        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbsBlock->addCrumb('home', array(
                'label'=>Mage::helper('catalog')->__('Home'),
                'title'=>Mage::helper('catalog')->__('Go to Home Page'),
                'link'=>Mage::getBaseUrl()
            ));


                $breadcrumbsBlock->addCrumb('chef', array(
                    'label'=> '厨师介绍',
                    'title'=> '厨师介绍',
                    'link'=> $this->getUrl('chef'),
                ));
            }

    }


}
