<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2012 Amasty (http://www.amasty.com)
* @package Amasty_Xnotif
*/   
class Amasty_Xnotif_EmailController extends Mage_Core_Controller_Front_Action
{
     public function stockAction()
    {
        $session = Mage::getSingleton('catalog/session');
        /* @var $session Mage_Catalog_Model_Session */
        $backUrl    = $this->getRequest()->getParam(Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED);
        $productId  = (int) $this->getRequest()->getParam('product_id');
        $guestEmail  = $this->getRequest()->getParam('guest_email');
        $parentId  = (int) $this->getRequest()->getParam('parent_id');
         
        if (!$backUrl) {
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
                ->setProductId($product->getId())
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
           
            if ($parentId){
                 $model->setParentId($parentId);
            }
	        $collection = Mage::getModel('productalert/stock')
                    ->getCollection()
                    ->addWebsiteFilter(Mage::app()->getWebsite()->getId())
		            ->addFieldToFilter('product_id', $productId)
                    ->addStatusFilter(0)
                    ->setCustomerOrder();

	        if($guestEmail) {
		        $customer = Mage::getModel('customer/customer') ;
	    	    $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
		        $customer->loadByEmail($guestEmail);
	        
		        if(!$customer->getId()){ 		
	                    $model->setEmail($guestEmail);
			            $collection->addFieldToFilter('email', $guestEmail);
		        }
		        else{
			        $model->setCustomerId($customer->getId());
			        $collection->addFieldToFilter('customer_id', $customer->getId());
		        }
	        }
            else {
		        $model ->setCustomerId(Mage::getSingleton('customer/session')->getId());
		    }
	    
            
	        if($collection->getSize() > 0) {
		        $session->addSuccess($this->__('Thank you! You are already subscribed to this product.'));
	         }
		    else{
		        $model->save();

		        Mage::dispatchEvent('product_alert_stock_subscription_save', array('source' => 'Product Alert', 'guest_email' => $guestEmail));
		        $session->addSuccess($this->__('Success. We\'ll email you as soon as this product is back in stock.'));
		    }
        }
        catch (Exception $e) {
            $session->addException($e, $this->__('Unable to update the alert subscription.'));
        }
        $this->_redirectReferer();
    }
}