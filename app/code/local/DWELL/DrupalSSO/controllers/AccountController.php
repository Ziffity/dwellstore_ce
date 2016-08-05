<?php

/**
 * Override Mage Code Account Controller.
 * Drupal SSO implementation.
 */

require_once 'Mage/Customer/controllers/AccountController.php';

class DWELL_DrupalSSO_AccountController extends Mage_Customer_AccountController
{

    /**
     * Login post action
    */
    public function loginPostAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_redirect('*/*/');
            return;
        }

        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $session = $this->_getSession();

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try { 
                    $session->login($login['username'], $login['password']);
                    if ($session->getCustomer()->getIsJustConfirmed()) {
                        $this->_welcomeCustomer($session->getCustomer(), true);
                    }
                } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $value = $this->_getHelper('customer')->getEmailConfirmationUrl($login['username']);
                            $message = $this->_getHelper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
							$resource = Mage::getSingleton('core/resource');
							$readConnection = $resource->getConnection('core_read');
						 	$readConnection = $resource->getConnection('core_read');
							$drupalusers = "SELECT pass FROM users where mail = '".$login['username']."'";
							//$drupalusers = "SELECT * FROM customer_entity INNER JOIN users ON customer_entity.email=users.mail WHERE customer_entity.email = '".$login['username']."'";
							$drupalresults = $readConnection->fetchRow($drupalusers);
							$hasedpassword = $this->_password_crypt('sha512', $login['password'], $drupalresults['pass']);
							if($hasedpassword!= '' & $hasedpassword == $drupalresults['pass']){
								$customer = Mage::getModel('customer/customer');
								$customer->setWebsiteId(Mage::app()->getWebsite()->getId());
 								$customer->loadByEmail($login['username']);
 								$session->setCustomerAsLoggedIn($customer->load($customer->getId()));
								$customer = Mage::getModel('customer/customer')->load($customer->getId());
          						$customer->setPassword($login['password']);
          						$customer->save();
								$this->_loginPostRedirect();
								return;
							} else {
                            $message = $e->getMessage();
                            break;
							}
                        default:
                            $message = $e->getMessage();
                    }
                    $session->addError($message);
                    $session->setUsername($login['username']);
                } catch (Exception $e) {
                    // Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
                }
            } else {
                $session->addError($this->__('Login and password are required.'));
            }
        }

        $this->_loginPostRedirect();
    }
	
	function _password_crypt($algo, $password, $setting) {
		  // Prevent DoS attacks by refusing to hash large passwords.
		  if (strlen($password) > 512) {
			return FALSE;
		  }
		  // The first 12 characters of an existing hash are its setting string.
		  $setting = substr($setting, 0, 12);
		
		  if ($setting[0] != '$' || $setting[2] != '$') {
			return FALSE;
		  }
		  $count_log2 = $this->_password_get_count_log2($setting);
		  // Hashes may be imported from elsewhere, so we allow != DRUPAL_HASH_COUNT
		 
		  $salt = substr($setting, 4, 8);
		  // Hashes must have an 8 character salt.
		  if (strlen($salt) != 8) {
			return FALSE;
		  }
		
		  // Convert the base 2 logarithm into an integer.
		  $count = 1 << $count_log2;
		
		  // We rely on the hash() function being available in PHP 5.2+.
		  $hash = hash($algo, $salt . $password, TRUE);
		  do {
			$hash = hash($algo, $hash . $password, TRUE);
		  } while (--$count);
		
		  $len = strlen($hash);
		  $output = $setting . $this->_password_base64_encode($hash, $len);
		  // _password_base64_encode() of a 16 byte MD5 will always be 22 characters.
		  // _password_base64_encode() of a 64 byte sha512 will always be 86 characters.
		  $expected = 12 + ceil((8 * $len) / 6);
		  return (strlen($output) == $expected) ? substr($output, 0, 55) : FALSE;
}

function _password_get_count_log2($setting) {
	  $itoa64 = $this->_password_itoa64();
	  return strpos($itoa64, $setting[3]);
}

function _password_itoa64() {
  	return './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
}

function _password_base64_encode($input, $count) {
	  $output = '';
	  $i = 0;
	  $itoa64 = $this->_password_itoa64();
	  do {
		$value = ord($input[$i++]);
		$output .= $itoa64[$value & 0x3f];
		if ($i < $count) {
		  $value |= ord($input[$i]) << 8;
		}
		$output .= $itoa64[($value >> 6) & 0x3f];
		if ($i++ >= $count) {
		  break;
		}
		if ($i < $count) {
		  $value |= ord($input[$i]) << 16;
		}
		$output .= $itoa64[($value >> 12) & 0x3f];
		if ($i++ >= $count) {
		  break;
		}
		$output .= $itoa64[($value >> 18) & 0x3f];
	  } while ($i < $count);
	
	  return $output;
}

}
