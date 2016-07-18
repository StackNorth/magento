<?php
class D1m_Chef_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Chef chef list page
     */
    public function indexAction() 
    {

        $collection = Mage::getModel('d1m_chef/chef')->getCollection()
            ->addFieldToFilter('cstatus', D1m_Chef_Model_Status::STATUS_ENABLED)            ;

        $chefId = $this->getRequest()->getParam('id');
        if ($chefId && $chefId>0)
        {
            $collection->addFieldToFilter('chef_id', $chefId);
        }
        $collection->setOrder('corder', 'ASC');

        Mage::register('current_chef_chefs', $collection);
		$this->loadLayout();     
		$this->renderLayout();
    }


    public function homepageAction()
    {
        // die('1234');
        //$this->loadLayout();
        echo $this->getLayout()->createBlock('d1m_chef/homepage') ->setBlockId('abc')->setTemplate('chef/homepage.phtml')->toHtml() ;
        // $this->renderLayout();
    }

    public function bigAction()
    {
        // die('1234');
        //$this->loadLayout();
        $collection = Mage::getModel('d1m_chef/chef')->getCollection()
            ->addFieldToFilter('cstatus', D1m_Chef_Model_Status::STATUS_ENABLED)            ;
        $chefId = $this->getRequest()->getParam('id');
           $collection->addFieldToFilter('chef_id', $chefId);
        Mage::register('current_chef_big', $collection);
        echo $this->getLayout()->createBlock('d1m_chef/big') ->setBlockId('abc')->setTemplate('chef/big.phtml')->toHtml() ;
        // $this->renderLayout();
    }



}
