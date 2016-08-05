<?php
class DWELL_Paypal_Helper_Data extends Mage_Core_Helper_Abstract
{
	
	 public function modifyRequest($request, $payment, $amount) {
    	
    	$request->setAmt($amount);
    	
    	$total_items 	= 0;
		$shipped_items 	= 0;
			
		foreach ($payment->getOrder()->getAllVisibleItems() as $item) {
			$total_items 	+= $item->getQtyOrdered();
			$shipped_items 	+= $item->getQtyShipped();
		}
			
		/* 	
			CAPTURECOMPLETE defaults to 'Y' - if not all items 
		   	have shipped, we need to flag not complete to allow multiple
			captures on the same authorization
		*/
		
		if ($shipped_items != $total_items) {
			$request->setCapturecomplete('N');
		}
    	
    	// If transaction is older than 29 days, set trx type to sale and invnum to random string
        // otherwise paypal will see the invoice number and think it's already been captured.
        $auth_time_limit = "-29 days";
    	if ($payment->getOrder()->getCreatedAt() < date('Y-m-d', strtotime($auth_time_limit))) {
    	  $request->setInvnum('elt-invoice-' . $payment->getOrder()->getIncrementId());
    	  $request->setTrxtype('S');
    	}
    	
    	return $request;
    }
    
   
    
}
	 