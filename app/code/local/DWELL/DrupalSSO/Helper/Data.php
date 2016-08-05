<?php

/**
 *
 */

class DWELL_DrupalSSO_Helper_Data extends Mage_Core_Helper_Abstract
{
	  // Shared secret key.
    public function getSharedSecret() {
        // See system.xml for config field
        return Mage::getStoreConfig('drupalsso_section/drupalsso_group/drupalsso_apikey');
    }

    // Drupal domain to use for authentication. Trailing slash required
    public function getDrupalSite() {
        // See system.xml for config field
        return Mage::getStoreConfig('drupalsso_section/drupalsso_group/drupalsso_drupalurl');
    }

    // Magento base URL
    public function getMagentoBaseUrl() {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'index.php/';
    }
    
     /**
     * Validate account by sending request to Drupal
     */
    public function validateAccount($encrypted_sid) {

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->getDrupalSite() . 'magento-check?magento_sid=' . $encrypted_sid);
        $header = array();
        $header[] = "Authorization: ".base64_encode($this->getSharedSecret());
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_HTTPGET, true);
        curl_setopt($curl, CURLOPT_SSLVERSION, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $reply = curl_exec($curl);

        $data = new stdClass();
        if ($reply === false) {
            Mage::throwException('Could not connect to Drupal Magento SSO API.');
        }
        else {
            $data = json_decode($reply);
            $data->status = curl_getinfo($curl, CURLINFO_HTTP_CODE); 
        }

        return $data;

    }
    
    /**
     * Detect previous page and urlencode it.
     */
    public function getPreviousPageUrl() {

        $url = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        return urlencode($url);

    }

    /**
     * Encrypt SID before passing to Drupal
     * It turns out base64 characters clash with the GET parameters.
     * http://stackoverflow.com/a/5835352
     */
    public function getEncryptedSid($magento_sid) {
      $iv=mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC));
      $sid = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->getSharedSecret(), $magento_sid, MCRYPT_MODE_CBC, 'ziffitydwellstor');
        return urlencode(base64_encode($sid));

    }
    
    public function getDrupalCustomerProfileLink() {
    
    	$session_id 	= Mage::getSingleton("core/session")->getEncryptedSessionId();
    	$sso_session	= $this->validateAccount(Mage::getSingleton('core/session')->getCustomSid());

        return $this->getDrupalSite() . 'user/' . $sso_session->user->drupal_uid . '/edit';
	}                		

}
