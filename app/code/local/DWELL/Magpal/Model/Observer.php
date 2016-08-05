<?php
class DWELL_Magpal_Model_Observer extends Varien_Event_Observer
{

	 public function updateCustomerNames($observer) {
		$order = $observer->getOrder();

		$cm = Mage::getModel('customer/customer');
		$cm->setWebsiteId(Mage::app()->getWebsite()->getId());
		$customer = $cm->loadByEmail($order->getCustomerEmail());

		if ($customer->getFirstname() == 'Your First Name' || $customer->getFirstname() == 'Guest') {
			
			$billing_address = $order->getBillingAddress();
			
			$order->setCustomerFirstname($billing_address->getFirstname());
			$order->setCustomerLastname($billing_address->getLastname());
			$order->save();
			
			$customer->setFirstname($billing_address->getFirstname());
			$customer->setLastname($billing_address->getLastname());
			$customer->save();
		} 
	 }
}
