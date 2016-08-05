<?php
class VINDESIGN_Updatepdp_Helper_Productinitbefore extends Mage_Core_Helper_Abstract
{
	public function Updatefromdrupal($observer)
	{
		$event = $observer->getEvent(); // Fetches the current event
    	$product = $event->getProduct();
    	if ($this->needsToBeUpdated($product)) {
    		
    		$vin_pdp = Mage::getModel('updatepdp/updatepdp');

    		$brand_id = $product->getBrandName();
    		if ($brand_id) {
    		
    		$brand_content = $vin_pdp->getDwellContent($brand_id);
    		if ($brand_content) {
    			$content = $brand_content->organization->description;
    			if ($brand_content->organization->url != '') {
    				$content .= '<div class="dwell_brand_link"><a href="' . $brand_content->organization->url
    						  . '" target="_blank">Learn More ></a></div>';
    			}
    			$product->setData('brand_text', $content)
   				->getResource()
    			->saveAttribute($product, 'brand_text');
    		}
    	}
    	
			$designer_id = $product->getDesignerName();
    		if ($designer_id) {
    		$designer_content = $vin_pdp->getDwellContent($designer_id,'designer');
    		if ($designer_content) {
    			$product->setData('designer_text', $designer_content->person->description)
   				->getResource()
    			->saveAttribute($product, 'designer_text');
    		}
    	}
    	}
	}
	
	private function needsToBeUpdated($product) {

		$now 			= mktime();
		$yesterday 		= strtotime('-1 day');
		$last_updated 	= strtotime($product->getDwellLastUpdateFromApi());

		if (!$last_updated || $last_updated < $yesterday) {
			$product->setData('dwell_last_update_from_api', $now)
   				->getResource()
    			->saveAttribute($product, 'dwell_last_update_from_api');
			return true;
		}
		return false;
	}
	
	private function Cutstring($str,$url)
	{
		$words_array = explode(" ", $str);
		$max = (count($words_array) >= 50) ? 50:count($words_array);
		$str = "";
		for($i=0;$i<=$max;$i++)
		{
			$str .= $words_array[$i];
			$str .= " ";
		}
		if(count($words_array) > 50) $str .= "<a class='readmore' href='".$url."' target='_blank'>Read More</a>";
		return $str;
	}
}