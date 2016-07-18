<?php
class D1m_Common_Block_Page_Html_Header extends Mage_Page_Block_Html_Header
{
    /**
     *
     * @return D1m_Customer_Model_Customer
     */
    public function getCustomer()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if ($customer && $customer->getId())
        {
            return $customer;
        }

        return ;
    }


    /**
     * @return string
     */
    public function getLogoutUrl()
    {
        return $this->getUrl('customer/account/logout');
    }

    /**
     * get account login url
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->getUrl('customer/account/login',array('_secure'=>true));
    }

    /**
     *  get  account register url
     * @return string
     */
    public function getRegisterUrl()
    {
        return $this->getUrl('customer/account/create',array('_secure'=>true));
    }

    /**
     *  get home page url
     * @return mixed
     */
    public function getHomeUrl()
    {
        return Mage::helper('d1m_common')->getDefaultWebsiteUrl();
    }

    /**
     * get account url
     */
    public function getAccountUrl()
    {
        return $this->getUrl('customer/account/index',array('_secure'=>true));
    }

    /**
     *  get checkout our cart url
     * @return string
     */
    public function getCheckoutUrl()
    {
         return $this->getUrl('checkout/cart',array('_secure'=>true));
    }

    /**
     *  get wishList url
     * @return string
     */
    public function getWishListUrl()
    {
        return $this->getUrl('wishlist/index');
    }

    /**
     * return true
     */
    public function isLogin()
    {
        /* @var $session Mage_Customer_Model_Session */
        $session = Mage::getSingleton('customer/session');

        return $session->isLoggedIn();
    }


}