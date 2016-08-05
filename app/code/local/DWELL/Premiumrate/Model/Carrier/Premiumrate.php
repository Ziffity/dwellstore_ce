<?php

require_once 'Webshopapps/Premiumrate/Model/Carrier/Premiumrate.php';

class DWELL_Premiumrate_Model_Carrier_Premiumrate extends Webshopapps_Premiumrate_Model_Carrier_Premiumrate
{
		
	protected function _getQuotes()
    {
		$rates = parent::_getQuotes();
		
		$longestDeliveryTime = Mage::helper("dwell_utility")->getLongestDeliveryTime($this->_rawRequest->getAllItems());
		
		if($longestDeliveryTime > 0) {
			$finalRates = array();
			
			foreach($rates as $rate){				
				if($rate['_data']['price'] == 0)
					$finalRates[] = $rate;				
			}
		}
		else {
			return $rates;
		}

	}
		
}