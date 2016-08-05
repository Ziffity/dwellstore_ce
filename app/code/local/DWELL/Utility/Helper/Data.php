<?php

class DWELL_Utility_Helper_Data extends Mage_Core_Helper_Abstract
{

	public function getLongestDeliveryTime($cartItems) {
		
		$escapeVals = array();
		$maxTime = 0;
		
		foreach ($cartItems as $item) {		
			
			$product = $item->getProduct();			
			$resource = $product->getResource();
			$elt = $resource->getAttributeRawValue($product->getId(), 'elt', Mage::app()->getStore());
			
			if($elt != 0 && $elt > $maxTime)
				$maxTime = $elt;
			
		}
		
		return $maxTime;
		
	}
	
	public function fixOrderForPayPalCancel($order) {
		
		$payment = $order->getPayment();
		$method = $payment->getMethod();		
				
		//it's not paypal payment, we do nothing	
		//method code is paypaluk_express, but just in case, there are other paypal methods available
		if(strpos($method, "paypal") === false)
			return;
		
		$now = time();
		$orderDate = strtotime($order->getCreatedAt());
		$daysBetween = ($now - $orderDate)/(60*60*24);
		
		//authorization is still valid, we do nothing
		if($daysBetween < 30)
			return;
		
		
		//we're fixing the transaction status so the cancel process can go through
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
		
		$binds = array($order->getId());
		$write->query("update sales_payment_transaction set is_closed=1 where order_id=? and txn_type='authorization'", $binds);
				
		return;
	}
	
	
	
	public function getFreeShippingCategoryId() {		
		return Mage::getResourceModel('catalog/category_collection')->addFieldToFilter('name', 'ExcludeFreeShipping')->getFirstItem()->getId();		
	}
	
	public function checkProductForFreeShipping($product) {
		
		$freeshippingCategoryId = $this->getFreeShippingCategoryId();
		
		$productCategoryIds = $product->getCategoryIds();
		
		if(in_array($freeshippingCategoryId, $productCategoryIds))
			return false;
		else
			return true;		
	}
	
	public function checkCartItemsForFreeShipping($items) {
		
		foreach($items as $i){
			if(! $this->checkProductForFreeShipping($i->getProduct()))
				return false;
		}
		
		return true;
	}
	
}


?>