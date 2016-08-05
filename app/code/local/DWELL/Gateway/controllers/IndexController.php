<?php


class DWELL_Gateway_IndexController extends Mage_Core_Controller_Front_Action
{

    public function zeroSubtotalAction() {
    
		$quote 		= Mage::getSingleton('checkout/session')->getQuote();
		$is_zero 	= $quote->getGrandTotal() == 0 ? true : false;
		$data 		= array('is_zero'=>$is_zero);
		print json_encode($data);
		exit();
     
    }
    
    public function cartQtyAction() {
    
		$quote = Mage::getSingleton('checkout/session')->getQuote();
		$subtotal = count($quote->getAllVisibleItems());
		$data 	= array('qty'=>$items_qty);
		print json_encode($data);
		exit();
     
    }


	public function giftAmountAction() {
		
		$quote = Mage::getSingleton('checkout/session')->getQuote();
		$giftAmount = $quote->getBaseGiftCardsAmountUsed();
		
		if($giftAmount) {
			$amount = Mage::helper('core')->currency($giftAmount, true);
			$data 	= array('gifttotal'=>$amount);
			print json_encode($data);
		}
		exit();
	}


}
