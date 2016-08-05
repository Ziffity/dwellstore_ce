<?php

/**
 * Override Mage Code Account Controller.
 * Drupal SSO implementation.
 */

require_once 'Mage/Checkout/controllers/CartController.php';

class DWELL_DrupalSSO_CartController extends Mage_Checkout_CartController
{

  	private function getHelper() {
  		return Mage::helper('drupalsso');
  	}

    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch() {

        parent::preDispatch();

        $action = $this->getRequest()->getActionName();
        $magento_session_id = Mage::getSingleton("core/session")->getEncryptedSessionId();

        $session = Mage::getSingleton('customer/session');

        if (isset($_GET['magento_url'])) {
            $session->setData("magento_internal_url", $_GET['magento_url']);
        }

        if (isset($_GET['return_url'])) {
            $session->setData("return_url", $_GET['return_url']);
        }

        // If user is not logged in and it's current location is customer/account/login
        if (!$session->isLoggedIn()) {

            $return_url = $this->getHelper()->getMagentoBaseUrl() . 'checkout/cart';
        
            $this->forceLogin($session, $magento_session_id, $return_url, $destination);

        }

    }

    /**
     * Create new customer account and force to login
     */
    private function forceLogin($session, $magento_session_id, $return_url, $destination = '') {

        $previous_url = $this->getHelper()->getPreviousPageUrl();

        $encrypted_sid = $this->getHelper()->getEncryptedSid($magento_session_id);

        // Retrieve user data if available
        $response = $this->getHelper()->validateAccount($encrypted_sid);

        if ($response->status == 200) {

            // User logged in to Drupal
            if ($response->valid_drupal_session && isset($response->user) && !empty($response->user->email)) {

                $customer = Mage::getModel('customer/customer');

                $customer->setWebsiteId(Mage::app()->getWebsite()->getId());

                $customer_email = $response->user->email;

                $customer->loadByEmail($customer_email);

                // Check whether Magento account exists or not
                if ($customer->getId()) {
                    try {
                        // If exists. Force login
                        $session->setCustomerAsLoggedIn($customer->load($customer->getId()));
                        // Add successful login message.
                        $session->addNotice($this->__($this->successLoginMessage));

                    }
                    catch (Exception $e) {
                        Mage::throwException('Could not set customer as logged in.');
                    }
                }
            }

        }
        else {
        	/* Todo - fix return path for guest checkout for now, return if not logged in to Drupal */
        	return;
        	
            $base_path = $this->getHelper()->getDrupalSite() . 'magento?sid=' . $encrypted_sid . '&magento_url=' . $previous_url;

            if (empty($destination)) {
                $this->_redirectUrl($base_path . '&return_url=' . $return_url);
            }
            else {
                $this->_redirectUrl($base_path . '&return_url=' . $return_url . '&dest=' . $destination);
            }
            return;

        }

    }

    /**
     * Detect Magento internal redirection path
     * this value comes from Drupal.
     */
    private function internalRedirectPath() {

        $session = $this->_getSession();
        $url = $session->getData("magento_internal_url");
        $session->setData("magento_internal_url", FALSE);
        if (!empty($url)) {
            // Prevent Magento redirecting back to home page
            // instead if user clicks sign in/regsiter from home page
            // Magento should show My Acount page.
            if ($url != Mage::getBaseUrl()) {
                //$session->addSuccess($this->__('Succesfully logged in'));
                $this->_redirectUrl(urldecode($url));
            }
            return;
        }

    }

    /**
     * Detect Drupal redirection path
     * this value comes from Drupal.
     * Currently we use this to redirect back to Drupal after logout from Drupal.
     */
    private function drupalRedirectPath($session) {

        $url = $session->getData("return_url");
        $session->setData("return_url", '');
        if (!empty($url)) {
            return urldecode($url);
        }
        return $this->getHelper()->getDrupalSite();

    }

    
}
