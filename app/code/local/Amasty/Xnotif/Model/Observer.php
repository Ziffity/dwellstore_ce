<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2012 Amasty (http://www.amasty.com)
* @package Amasty_Xnotif
*/  
class Amasty_Xnotif_Model_Observer extends Mage_ProductAlert_Model_Observer
{
    protected function _processStock(Mage_ProductAlert_Model_Email $email)
    {
        $email->setType('stock');
        foreach ($this->_getWebsites() as $website) {
            /* @var $website Mage_Core_Model_Website */

            if (!$website->getDefaultGroup() || !$website->getDefaultGroup()->getDefaultStore()) {
                continue;
            }
            if (!Mage::getStoreConfig(self::XML_PATH_STOCK_ALLOW, $website->getDefaultGroup()->getDefaultStore()->getId())) {
                continue;
            }
            try {
                $collection = Mage::getModel('productalert/stock')
                    ->getCollection()
                    ->addWebsiteFilter($website->getId())
                    ->addStatusFilter(0)
                    ->setCustomerOrder();
            }
            catch (Exception $e) {
                Mage::log($e->getMessage());
                $this->_errors[] = $e->getMessage();
                return $this;
            }
            $previousCustomer = null;
            $email->setWebsite($website);
            foreach ($collection as $alert) {
                try {
                    $isGuest =0;
                    if (0 == $alert->getCustomerId()) $isGuest = 1;
                    if (!$previousCustomer || ($previousCustomer->getId() != $alert->getCustomerId()) || ($previousCustomer->getEmail() != $alert->getEmail())) {
                        if($isGuest){
                                $customer = Mage::getModel('customer/customer') ;
				$customer->setWebsiteId(Mage::app()->getWebsite()->getId());
				$customer->loadByEmail($alert->getEmail());
				if(!$customer->getId()){ 
	                                $customer->setEmail($alert->getEmail());
	                                $customer->setFirstname(Mage::helper('amxnotif')->__('Dear Friend'));
	                                $customer->setGroupId(0);
					$customer->setId(0);
				}
                        }
                        else{
                            $customer = Mage::getModel('customer/customer')->load($alert->getCustomerId());
                        }
                        if ($previousCustomer) {
                            $email->send();
                            $this->unsubscribe($alert->getProductId(), $email->getCustomer(), $isGuest);
                        }

                        if (!$customer) {
                            continue;
                        }
                        $previousCustomer = $customer;
                        $email->clean();
                        $email->setCustomer($customer);
                    }
                    else {
                        $customer = $previousCustomer;
                    }

                    $product = Mage::getModel('catalog/product')
                        ->setStoreId($website->getDefaultStore()->getId())
                        ->load($alert->getProductId());
                    /* @var $product Mage_catalog_Model_Product */
                    
                    
                    if (!$product) {
                        continue;
                    }   
                    $product->setCustomerGroupId($customer->getGroupId());
                    if ($product->isSalable() || $product->isSaleable()) {
			if($alert->getParentId()){
                        		$product = Mage::getModel('catalog/product') 
						->setStoreId($website->getDefaultStore()->getId())
                        			->load($alert->getParentId());    
                   		 }

                        $email->addStockProduct($product);
                        $alert->setSendDate(Mage::getModel('core/date')->gmtDate());
                        $alert->setSendCount($alert->getSendCount() + 1);
                        $alert->setStatus(1);
                        $alert->save();
                    }
                }
                catch (Exception $e) {
                    Mage::log($e->getMessage());
                    $this->_errors[] = $e->getMessage();
                }
            }
            if ($previousCustomer) {
                try {
                    $email->send();
                    $this->unsubscribe($alert->getProductId(), $email->getCustomer(), $isGuest);
                }
                catch (Exception $e) {
                    Mage::log($e->getMessage());
                    $this->_errors[] = $e->getMessage();
                }
            }
        }
        return $this;
    }
    
    public function handleBlockAlert($observer) 
    {
        /* @var $block Mage_Core_Block_Abstract */
        $block = $observer->getBlock();
        
        $transport = $observer->getTransport();
        $html = $transport->getHtml();
        $pos = strpos($html, 'alert-stock');
        if ($block instanceof Mage_Productalert_Block_Product_View && $pos && !Mage::getStoreConfig('amxnotif/general/disable_guest')) {
            $isLogged = Mage::helper('customer')->isLoggedIn();
            if(!$isLogged) {
                preg_match('#product_id/([0-9]+)/#', $html, $result);
                if($result) { 
                    $result = array();
                    $product = Mage::registry('current_product');
                    if (!$product->isSaleable()){
                        $blockHtml = Mage::helper('amxnotif')->getStockAlert($product, $isLogged, 1);
                        $html = $blockHtml;
                        $transport->setHtml($html);
                    }
                }
            }
                
        }
    }
    
    private function unsubscribe($productId, $customer, $isGuest) 
    {
	try {
        if (!$productId || (!$isGuest && !Mage::getStoreConfig('amxnotif/general/unsubscribeC')) || ($isGuest && !Mage::getStoreConfig('amxnotif/general/unsubscribeG'))) {
                return;
            }
           $product = Mage::getModel('catalog/product')->load($productId);
           if (!$product->getId() || !$product->isVisibleInCatalog()) {        
                Mage::log('The product was not found.');
                return ;
            }
	    $_customerId = 0;
	    if(!$isGuest && $customer && $customer->getId())
            	$_customerId = $customer->getId();
            $model  = Mage::getModel('productalert/stock')
                ->setCustomerId($_customerId)
                ->setProductId($product->getId())
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByParam();
            if ($model->getId()) {
                $model->delete();
		  return true;
            }
        }
        catch (Exception $e) {
             Mage::log($e->getMessage());
             Mage::log('Unable to update the alert subscription.');
		return false;
        }
	 return false;
    }
}