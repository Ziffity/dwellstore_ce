<?php

// Extending from custom controller to allow for check against existing subscriptions.
include_once('DWELL/Newsletter/controllers/SubscriberController.php');

class ES_Newssubscribers_SubscriberController extends DWELL_Newsletter_SubscriberController
{
    public function newAction($source = "Popup")
    {
    	if ($this->getRequest()->getPost('source')) {
    	  $source = $this->getRequest()->getPost('source');
    	}
        parent::newAction($source);
    
        /* 
          DWELL - Newsletter is not actually active, but we still want to send the email with
          the coupon code.
           
        if (!Mage::getStoreConfig('newsletter/common/isactive'))
            return '';
        */

        $session = Mage::getSingleton('core/session');
        $errorMsg = '';
        $errors = $session->getMessages(false)->getErrors();
        $email = (string) $this->getRequest()->getPost('email');

        if ($errors)
            $errorMsg = $errors[0]->getText();

        if (!$errorMsg) {
            try {
				$couponCode = Mage::getModel('newsletter/subscriber')->getCouponCode();
                $mailTemplate = Mage::getModel('core/email_template');
                
                $template_id = (int) Mage::getStoreConfig('newsletter/coupon/template');

                if ($template_id) {
                	$mailTemplate->sendTransactional($template_id, array(
                    	'name' => Mage::getStoreConfig('trans_email/ident_general/name'),
                    	'email' => Mage::getStoreConfig('trans_email/ident_general/email')
                	), $email, 'newsletter_subscr_coupon', array(
                    	'couponCode' => $couponCode
                	));
                }            
                $this->storeCouponCode($email);
                $session->addSuccess($this->__('Your 15% off promo code will arrive in your inbox very soon. <br />Hotmail users: add mail@dwellmedia.com to your address book for white listing.'));
            }
            catch (Mage_Core_Exception $e) {
                $session->addException($e, $this->__('There was a problem with the subscription: %s', $e->getMessage()));
            }
            catch (Exception $e) {
                $session->addException($e, $this->__('There was a problem with the subscription.'));
            }
        }
    }

    public function newajaxAction()
    {
        $this->newAction();
        $session = Mage::getSingleton('core/session');
        $messages = $session->getMessages(true);
        $errors = $messages->getErrors();
        $response = array(
            'errorMsg' => '',
            'successMsg' => ''
        );

        if ($errors) {
            $response['errorMsg'] = $errors[0]->getText();
        } else {
            $success = $messages->getItemsByType('success');
            for ($s=0; $s < count($success); $s++) {
              $response['successMsg'] .= $success[$s]->getText() . "<br />";
            }
        }
        echo Mage::helper('core')->jsonEncode($response);
        exit;
    }

	private function storeCouponCode($email){
	    $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
		$coupon_code = Mage::registry('coupon_code');
		$subscriber->setNewsletterCouponCode($coupon_code)->save();
	}
}
