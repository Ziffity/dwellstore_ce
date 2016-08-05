<?php 

class DWELL_Magpal_CustomerController extends Mage_Core_Controller_Front_Action
{
	/* pull unique code from request headers */
	public function preDispatch()
	{
   	 	$request = new Zend_Controller_Request_Http();

   		if ($request->getHeader('Authentication') != 'spYdI5T2xpjEVzTc3mdJZCTq') {
	   		$response = $this->getResponse();
			$response->setHttpResponseCode(401);
			$content = array('error'=>"Not Authorized: You must set a header called 'Authentication'.");
			$response->setBody(json_encode($content));
			$response->sendResponse();
			exit;
		}
		
	}
	
    public function cartcountAction(){
    
    	$params = $this->getRequest()->getParams();
    	$response = $this->getResponse();
		
		$response->setHeader('HTTP/1.1 200 OK','');
		$response->setHeader('Content-type', 'application/json');	
		$quote = Mage::getModel('sales/quote')->getCollection()
				->addFieldToFilter('customer_id',$params['id'])
				->addFieldToFilter('is_active',1)
				->getLastItem();

		if ($quote) {
			$cart_content = array('items_count'=>intval($quote->getItemsCount()));
		} else {
			$cart_content = "No active cart";
		}
		
		$content = json_encode($cart_content);
		
        $response->setBody($content);
        $response->sendResponse();
       	exit;

    }
}