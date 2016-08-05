<?php
class DWELL_Signup_Model_Observer
{

	public function addToDwellSignup(Varien_Event_Observer $observer) {

      // Add $observer->getData('guest_email') to newsletter
      $email = $observer->getData('guest_email');
      
      $newsletter = Mage::getModel('newsletter/subscriber');
      $subscriber = $newsletter->loadByEmail($email);

      // Only subscribe new email accounts to Mage Newsletter
      $id = $subscriber->getSubscriberId();
      if (!$subscriber->getSubscriberId()) {
        $subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);
        $subscriber->setSubscriberEmail($email);
        $subscriber->setStoreId(Mage::app()->getStore()->getId());
        $subscriber->setSubscriberConfirmCode($subscriber->RandomSequence());
        $subscriber->save();
        $id = $newsletter->loadByEmail($email)->getSubscriberId();
      }
            
      $record = Mage::getModel('signup/signup')->getCollection()
        ->addFieldToFilter('newsletter_subscriber_id', $id)
        ->getFirstItem();
        
      // Only add original signup time and source
      if (!$record->getDwellSignupId()) {
        $new_signup = Mage::getModel('signup/signup');
      	$new_signup->setNewsletterSubscriberId($id);
        $new_signup->setSource($observer->getData('source'));
      	$new_signup->save();
      }
        
	}
		
}
