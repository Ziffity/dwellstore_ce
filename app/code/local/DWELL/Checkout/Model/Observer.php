<?php
/**
*  DWELL-821, moving subscription discount to code to apply discount to 
*  line item rather than whole cart.
*
**/
class DWELL_Checkout_Model_Observer
{
  const SUBSCRIPTION_SKU  = '111460000000';
  const DISCOUNT_AMT      = 5;
  
  public function checkForSubscription() {
    $quote = Mage::getSingleton('checkout/session')->getQuote();

    // Save the quote to start a session
    $quote->save();
    $cartItems = $quote->getAllVisibleItems();
    
    $subscriptionItem = $this->getSubscriptionItem($cartItems);
    
    if ($subscriptionItem) { 
      $pm = Mage::getModel('catalog/product');
      $product_id = $pm->getIdBySku(self::SUBSCRIPTION_SKU);
      $product = $pm->load($product_id);
      $price = $product->getPrice();
      
      if (count($cartItems) >= 2) {
        $price -= self::DISCOUNT_AMT;
      }
      $subscriptionItem->setCustomPrice($price);
      $subscriptionItem->setOriginalCustomPrice($price);
      $subscriptionItem->save();
    }
    
    $quote->save();
    return true;
}
    
  protected function getSubscriptionItem($cartItems) {
    foreach ($cartItems as $item) {
      if ($item->getSku() == self::SUBSCRIPTION_SKU) {
        return $item;
      }
    }
    return false;
  }
}