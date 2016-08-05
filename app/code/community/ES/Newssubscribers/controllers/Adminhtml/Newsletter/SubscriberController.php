<?php
include_once('Mage/Adminhtml/controllers/Newsletter/SubscriberController.php');
class ES_Newssubscribers_Adminhtml_Newsletter_SubscriberController extends  Mage_Adminhtml_Newsletter_SubscriberController
{
    public function resendAction()
    { 
           
  		$subscriber_id=$this->getRequest()->getParam('subscriber_id', false);
		
		$model=Mage::getModel('newsletter/subscriber')->load($subscriber_id);
	
		$coupon_code=$model->getData('newsletter_coupon_code');
		$oCoupon = Mage::getModel('salesrule/coupon')->load($coupon_code, 'code');
			if($oCoupon->getTimesUsed()==0){
				$email=$model->getEmail();
				$model->delete();
				$oCoupon->delete();

				$customer_id=$model->getData('customer_id');
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

				$status = Mage::getModel('newsletter/subscriber')->subscribe($email);
				if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
					Mage::getSingleton('adminhtml/session')->addSuccess('Confirmation request has been sent.');
				}
				else {
					$this->storeCouponCode($email,$customer_id);
					Mage::getSingleton('adminhtml/session')->addSuccess('successfully sent');
				}
						
			}
			else{
				Mage::getSingleton('adminhtml/session')->addError('Sorry Already Coupon has been used.');
			}

		
		$this->_redirect('*/*/');
    }

	private function storeCouponCode($email,$customer_id){
	    $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
		$coupon_code = Mage::registry('coupon_code');
		$subscriber->setCustomerId($customer_id)->setNewsletterCouponCode($coupon_code)->save();
	}
 
}
