<?php

class VINDESIGN_Updatepdp_Model_Updatepdp extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('updatepdp/updatepdp');
    }
    
    protected function getUrl($id,$type) {
    	return $this->getDrupalSite() . "magento-info/$type/$id";
    }	
    
    public function getDwellContent($id, $type='brand') {

		$client = new Varien_Http_Client($this->getUrl($id,$type));
        $client->setMethod(Varien_Http_Client::GET);
        $client->setConfig(array('timeout' => 30));
        $client->setHeaders(array('Authorization' => base64_encode($this->getSharedSecret())));

        $data = new stdClass();

        try {
            $response = $client->request();
            $data = json_decode($response->getBody());
            $data->status = $response->getStatus();
        }
        catch (Exception $e) {
            Mage::throwException('Could not connect to Drupal Magento SSO API.');
        }

        return $data;
            
    }
    
    private function getSharedSecret() {
    	return Mage::getStoreConfig('drupalsso_section/drupalsso_group/drupalsso_apikey');
    }
    
    private function getDrupalSite() {
    	return Mage::getStoreConfig('drupalsso_section/drupalsso_group/drupalsso_drupalurl');
    }
}