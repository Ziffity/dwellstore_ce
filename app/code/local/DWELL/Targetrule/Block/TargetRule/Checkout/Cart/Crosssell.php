<?php
class DWELL_Targetrule_Block_TargetRule_Checkout_Cart_Crosssell extends Enterprise_TargetRule_Block_Checkout_Cart_Crosssell
{

	const DWELL_PRODUCT_SKU = '116160000000';
	
	public function showPromo() {
		return ($this->hasValidItem() && !$this->hasSku());
    }
    
    public function getSkuId() {
    	return Mage::getModel('catalog/product')->getIdBySku(self::DWELL_PRODUCT_SKU);
    }
    
    protected function hasValidItem() {
    	$items = $this->getCartItems();
    	foreach ($items as $item) {
    		if ($item->getSku() != self::DWELL_PRODUCT_SKU) {
    			return true;
    		}
    	}
    }
    
    protected function hasSku() {
    	$items = $this->getCartItems();
    	foreach ($items as $item) {
    		if ($item->getSku() == self::DWELL_PRODUCT_SKU) {
    			return true;
    		}
    	}
    	return false;
    }
    
    private function getCartItems() {
      	$quote = Mage::getSingleton('checkout/session')->getQuote();
    	return $quote->getAllVisibleItems();
    }
}
			