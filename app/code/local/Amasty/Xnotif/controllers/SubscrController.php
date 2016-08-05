<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2012 Amasty (http://www.amasty.com)
* @package Amasty_Xnotif
*/   
class Amasty_Xnotif_SubscrController extends Mage_Core_Controller_Front_Action
{
    public function preDispatch()
    {
        parent::preDispatch();

        $loginUrl = Mage::helper('customer')->getLoginUrl();
        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }
    
    public function indexAction() 
    {
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');

        $this->getLayout()->getBlock('head')->setTitle($this->__('My Out of Stock Subscriptions'));
        $this->renderLayout();
    }
    
    public function removeAction()
    {
        $id = (int) $this->getRequest()->getParam('item');
        $item = Mage::getModel('productalert/stock')->load($id);
	$_customer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
		     ->load(Mage::getSingleton('customer/session')->getCustomer()->getId());
	// check if not a guest subscription (cust. id is set) and is matching with logged in customer
	if ( $item->getCustomerId() > 0 && $item->getCustomerId() == $_customer->getId() ){
	    try {
	        $item->delete();
	    }
	    catch (Mage_Core_Exception $e) {
	        Mage::getSingleton('customer/session')->addError(
	            $this->__('An error occurred while deleting the item from Subscriptions: %s', $e->getMessage())
	        );
	    }
	    catch(Exception $e) {
	        Mage::getSingleton('customer/session')->addError(
	            $this->__('An error occurred while deleting the item from Subscriptions.')
	        );
	    }
	}
        $this->_redirectReferer(Mage::getUrl('*/*'));
    }
    
    public function stockAction()
    {
        $session = Mage::getSingleton('catalog/session');
        /* @var $session Mage_Catalog_Model_Session */
        $backUrl    = $this->getRequest()->getParam(Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED);
        $productId  = (int) $this->getRequest()->getParam('product_id');
        $parentId  = (int) $this->getRequest()->getParam('parent_id');
        
        if (!$backUrl || !$productId) {
            $this->_redirect('/');
            return ;
        }

        if (!$product = Mage::getModel('catalog/product')->load($productId)) {
            /* @var $product Mage_Catalog_Model_Product */
            $session->addError($this->__('Not enough parameters.'));
            $this->_redirectUrl($backUrl);
            return ;
        }
        try {
            $model = Mage::getModel('productalert/stock')
                ->setCustomerId(Mage::getSingleton('customer/session')->getId())
                ->setProductId($product->getId())
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
            
            if ($parentId){
                 $model->setParentId($parentId);
            }
            $model->save();
            $session->addSuccess($this->__('Alert subscription has been saved.'));
        }
        catch (Exception $e) {
            $session->addException($e, $this->__('Unable to update the alert subscription.'));
        }
        $this->_redirectReferer();
    }
} 