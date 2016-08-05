<?php
class Jaljale_Pixel_Helper_Data extends Mage_Core_Helper_Abstract
{
	public static $_store = '';

    protected function _getStore()
    {
        if(self::$_store){
            self::$_store = Mage::app()->getStore()->getStoreId();
        }

        return self::$_store;
    }

	public function isAudienceEnable()
    {
        return Mage::getStoreConfig('pixal/audience/enable', $this->_getStore());
    }

    public function getAudienceCode()
    {
    	return Mage::getStoreConfig('pixal/audience/code', $this->_getStore());
    }

    public function isTrackingEnable()
    {
    	return Mage::getStoreConfig('pixal/tracking/enable', $this->_getStore());
    }

    public function getTrackingCode()
    {
    	return Mage::getStoreConfig('pixal/tracking/code', $this->_getStore());
    }

    public function getCurrency()
    {
    	return Mage::getStoreConfig('pixal/tracking/currency', $this->_getStore());
    }

   

    public function getPageName()
    {
        //home, category, product, cart, purchase, siteview
        //purchase = success pages
        //Siteview = all other pages not contained in home, category, product, cart, purchase
        //checkout = checkout

        $module = Mage::app()->getRequest()->getModuleName();
        $controller = Mage::app()->getRequest()->getControllerName();
        $action = Mage::app()->getRequest()->getActionName();

        if ('cms' == $module && 'index' == $controller && 'index' == $action)
        {
            $pageName = 'home';
        }
        else if ('catalog' == $module && 'product' == $controller && 'view' == $action)
        {
            $pageName = 'product';
            $product = Mage::getModel('catalog/product')->load(
                Mage::app()->getRequest()->getParam('id'));
            $ean = $product->getSku();
        }
        else if ('checkout' == $module && 'onepage' == $controller && 'index' == $action)
        {
            if (!Mage::getSingleton('customer/session')->isLoggedIn())
            {
                $pageName = 'cart';
            } else {
                $pageName = 'checkout';
            }
        }
        else if ('checkout' == $module && 'onepage' == $controller && 'success' == $action)
        {
            $pageName = 'purchase';
        }
        else
            $pageName = 'siteview';

        return $pageName;
    }
}
	