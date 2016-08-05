<?php

/**
 * Override Mage Newsletter Subscription Controller to allow check if already subscribed.
 */

require_once 'Mage/Newsletter/controllers/SubscriberController.php';

class DWELL_Newsletter_SubscriberController extends Mage_Newsletter_SubscriberController  {

     /**
      * New subscription action
      */
    public function newAction($source = null)
    {
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $session            = Mage::getSingleton('core/session');
            $customerSession    = Mage::getSingleton('customer/session');
            $email              = (string) $this->getRequest()->getPost('email');

            try {
                if (!Zend_Validate::is($email, 'EmailAddress')) {
                    Mage::throwException($this->__('Please enter a valid email address.'));
                }

                if (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1 && 
                    !$customerSession->isLoggedIn()) {
                    Mage::throwException($this->__('Sorry, but administrator denied subscription for guests. Please <a href="%s">register</a>.', Mage::helper('customer')->getRegisterUrl()));
                }

                $ownerId = Mage::getModel('customer/customer')
                        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                        ->loadByEmail($email)
                        ->getId();
                if ($ownerId !== null && $ownerId != $customerSession->getId()) {
                    Mage::throwException($this->__('This email address is already assigned to another user.'));
                }
                
                // Adding check if already subscribed
                if ($this->isSubscribed($email)) {
					Mage::throwException($this->__('This address is already subscribed.'));
                }

                $status = Mage::getModel('newsletter/subscriber')->subscribe($email, $source);
                if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
                    $session->addSuccess($this->__('Confirmation request has been sent.'));
                }
                else {
                    $session->addSuccess($this->__('Thank you for your subscription.'));
                }
            }
            catch (Mage_Core_Exception $e) {
                $session->addException($e, $this->__('There was a problem with the subscription: %s', $e->getMessage()));
            }
            catch (Exception $e) {
                $session->addException($e, $this->__('There was a problem with the subscription.'));
            }
        }
        $this->_redirectReferer();
    }
    
    public function isSubscribed($email) {
      	$record = Mage::getModel('newsletter/subscriber')
      		->getCollection()
      		->addFieldtoFilter('subscriber_email', $email)
      		->getFirstItem();
      	if ($record->getSubscriberId()) {
      	  return true;
      	}
      	return false;
    }
}
