<?php

include_once('Mage/Adminhtml/controllers/Sales/Order/EditController.php');
class Oeditor_Ordereditor_Order_EditController extends Mage_Adminhtml_Sales_Order_EditController
{ 
    /**
     * Saving quote and create order
     */
    public function saveAction()
		{
			
			  if(isset($_POST['edit_order_number']) && $_POST['edit_order_number'] != "")
			 {
			
				$postOrder = $this->getRequest()->getPost('order');
		
				
				$oldOrder = $this->_getSession()->getOrder();
				$preTotal = $oldOrder->getGrandTotal();
						
				// echo $oldOrder->getId();die;
				//		$quote = Mage::getModel('sales/quote_item')->getQuote();
		
				$quote = $this->_getSession()->getQuote();
				//$tQ = $this->prepareEditedQuoteItems();
				//$quote = $this->_getSession()->getQuote();
				
				$oldOrder = $this->_getSession()->getOrder();
		//	echo $quote->getPayment();die;
		
		//	Mage::getModel('sales/order')	
		
		
				
				$oldOrderId = $oldOrder->getId();
				$order = Mage::getModel('sales/order')->load($oldOrderId);
				$orderAllItems = $order->getAllItems();
				foreach($orderAllItems as $delteItem)
				{
					$delteItem->delete();
				}
		
				$convertor = Mage::getModel('sales/convert_quote');
				 $price = 0 ;
				foreach ($quote->getAllItems() as $item) {
				
					$options = array();
					$productOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
			//		echo '<pre>';print_r($productOptions);
					if ($productOptions) {
						$productOptions['info_buyRequest']['options'] = $this->prepareEditedOptionsForRequest($item);
						$options = $productOptions;						
					}
					$addOptions = $item->getOptionByCode('additional_options');
					if ($addOptions) {
						$options['additional_options'] = unserialize($addOptions->getValue());
					}
					$item->setProductOrderOptions($options);
		//			echo $item->getItemCustomPrice().$item->getCustomPrice();die;
					$orderItem = $convertor->itemToOrderItem($item);
		
		//			echo '<pre>';print_r($orderItem);die;
					if ($item->getParentItem()) {
						$orderItem->setParentItem($oldOrder->getItemByQuoteItemId($item->getParentItem()->getId()));
					}
					$oldOrder->addItem($orderItem);
					$orderItem->save();
				}
				
				$address = $quote->getShippingAddress();
				$taxAmount = $address->getTaxAmount();
if(isset($postOrder['shipping_method']) && $postOrder['shipping_method'] != "") // check if shipping method available(usually shipping method does not available for virtual products)
{
				$rates = $address->getShippingRatesCollection();
					
					$taxAmount = $address->getTaxAmount();
			//		$_rates = $quote->getShippingAddress()->getShippingRatesCollection();
		
			$shippingRates = array();
			$shippingPrice = 0 ;
			foreach ($rates as $_rate)
			{
				
				$orderData = $this->getRequest()->getPost('order');
				
					if($orderData['shipping_method'] == $_rate->getCode())
					{
						$oldOrder->setShippingMethod($orderData['shipping_method']);
						$shippingDescription = $_rate->getCarrierTitle().':';
						$oldOrder->setShippingDescription($shippingDescription);
						
						$oldOrder->setShippingAmount($_rate->getPrice());
						$oldOrder->setShippingInclTax($_rate->getPrice());
						$oldOrder->setBaseShippingInclTax($_rate->getPrice());	
						$shippingPrice = $_rate->getPrice();
					}
			}
}
				$oldOrder->setData('coupon_code',$quote->getData('coupon_code'));
				$oldOrder->setData('store_id',$quote->getData('store_id'));
				$oldOrder->setData('subtotal',$quote->getData('subtotal'));
				
				$subTotal = $quote->getData('subtotal');
				$baseSubTotal = $quote->getData('base_subtotal_with_discount');
				$discountAmount = $subTotal - $baseSubTotal;
				$discountAmount = '-'.$discountAmount;
				
				$oldOrder->setData('discount_amount',$discountAmount);
				$oldOrder->setData('base_discount_amount',$quote->getData('subtotal'));
				$oldOrder->setData('discount_description',$quote->getData('coupon_code'));
				
				
				$oldOrder->setData('base_subtotal',$quote->getData('base_subtotal'));
				$oldOrder->setData('grand_total',$quote->getData('grand_total'));
				$oldOrder->setData('base_grand_total',$quote->getData('base_grand_total'));
				$oldOrder->setData('store_id',$quote->getData('store_id'));
				$oldOrder->setData('base_tax_amount',$taxAmount);
				$oldOrder->setData('tax_amount',$taxAmount);
				
				$quote->getPayment()->getMethod();
		
		
			$sameAsBilling = $quote->getShippingAddress()->getSameAsBilling();
		
		
			$postBillingAddress = $postOrder['billing_address'];
			$bb = $oldOrder->getBillingAddress();
			
			$bb->setData('prefix',$postBillingAddress['prefix']);
			$bb->setData('firstname',$postBillingAddress['firstname']);
			$bb->setData('middlename',$postBillingAddress['middlename']);
			$bb->setData('lastname',$postBillingAddress['lastname']);
			$bb->setData('suffix',$postBillingAddress['suffix']);
			$bb->setData('company',$postBillingAddress['company']);
			
			$bb->setData('street',implode(" ",$postBillingAddress['street']));
			
			$bb->setData('city',$postBillingAddress['city']);
			$bb->setData('country_id',$postBillingAddress['country_id']);
			
			if(isset($postBillingAddress['region']) && $postBillingAddress['region'] != "")
			{
				$bb->setData('region',$postBillingAddress['region']);
			}
			if(isset($postBillingAddress['region_id']) && $postBillingAddress['region_id'] != "")
			{
				$bb->setData('region_id',$postBillingAddress['region_id']);			
			}

			$bb->setData('postcode',$postBillingAddress['postcode']);
			$bb->setData('telephone',$postBillingAddress['telephone']);
			$bb->setData('fax',$postBillingAddress['fax']);
			if(isset($postBillingAddress['vat_id']) && $postBillingAddress['vat_id'] != "")
			{
				$bb->setData('vat_id',$postBillingAddress['vat_id']);
			}
			
							
			$oldOrder->setBillingAddress($bb);
			
			$sameShip = $oldOrder->getShippingAddress();
			if($sameAsBilling == 1 && isset($sameShip) && is_array($sameShip))
			{
			
				$sameShip = $oldOrder->getShippingAddress();
			
				$sameShip->setData('prefix',$postBillingAddress['prefix']);
				$sameShip->setData('firstname',$postBillingAddress['firstname']);
				$sameShip->setData('middlename',$postBillingAddress['middlename']);
				$sameShip->setData('lastname',$postBillingAddress['lastname']);
				$sameShip->setData('suffix',$postBillingAddress['suffix']);
				$sameShip->setData('company',$postBillingAddress['company']);
				
				$sameShip->setData('street',implode(" ",$postBillingAddress['street']));
				
				$sameShip->setData('city',$postBillingAddress['city']);
				$sameShip->setData('country_id',$postBillingAddress['country_id']);
				if(isset($postBillingAddress['region']) && $postBillingAddress['region'] != "")
				{
					$sameShip->setData('region',$postBillingAddress['region']);
				}
				if(isset($postBillingAddress['region_id']) && $postBillingAddress['region_id'] != "")
				{
					$sameShip->setData('region_id',$postBillingAddress['region_id']);
				}
				
				$sameShip->setData('postcode',$postBillingAddress['postcode']);
				$sameShip->setData('telephone',$postBillingAddress['telephone']);
				$sameShip->setData('fax',$postBillingAddress['fax']);
				if(isset($postBillingAddress['vat_id']) && $postBillingAddress['vat_id'] != "")
				{
					$sameShip->setData('vat_id',$postBillingAddress['vat_id']);
				}	
			
				$oldOrder->setShippingAddress($sameShip);
			}
			
			if(isset($postOrder['shipping_address']) && is_array($postOrder['shipping_address']))
			{
				$shipAdd = $oldOrder->getShippingAddress();
				$postShippingAddress = $postOrder['shipping_address'];
				
				$shipAdd->setData('prefix',$postShippingAddress['prefix']);
				$shipAdd->setData('firstname',$postShippingAddress['firstname']);
				$shipAdd->setData('middlename',$postShippingAddress['middlename']);
				$shipAdd->setData('lastname',$postShippingAddress['lastname']);
				$shipAdd->setData('suffix',$postShippingAddress['suffix']);
				$shipAdd->setData('company',$postShippingAddress['company']);
				
				$shipAdd->setData('street',implode(" ",$postShippingAddress['street']));
				
				$shipAdd->setData('city',$postShippingAddress['city']);
				$shipAdd->setData('country_id',$postShippingAddress['country_id']);
				if(isset($postShippingAddress['region']) && $postShippingAddress['region'] != "")
				{
					$shipAdd->setData('region',$postShippingAddress['region']);
				}
				if(isset($postShippingAddress['region_id']) && $postShippingAddress['region_id'] != "")
				{
					$shipAdd->setData('region_id',$postShippingAddress['region_id']);
				}
				
				$shipAdd->setData('postcode',$postShippingAddress['postcode']);
				$shipAdd->setData('telephone',$postShippingAddress['telephone']);
				$shipAdd->setData('fax',$postShippingAddress['fax']);
				if(isset($postShippingAddress['vat_id']) && $postShippingAddress['vat_id'] != "")
				{
					$shipAdd->setData('vat_id',$postShippingAddress['vat_id']);
				}
				
								
				$oldOrder->setShippingAddress($shipAdd);
			}
		
			$comment = $postOrder['comment'];
			if(isset($comment) && is_array($comment))
			{
				$customer_note = $comment['customer_note'];
				if(isset($customer_note) && $customer_note != "")
				{
					$oldOrder->setCustomerNote($customer_note);
					$oldOrder->addStatusToHistory($oldOrder->getStatus(),$customer_note, false);
				}
			}
		
			$account = $postOrder['account'];
			if(isset($account) && is_array($account))
			{
					$email = $account['email'];
					if(isset($email) && $email != "")
					{
						$oldOrder->setCustomerEmail($email);
					}
					
					
					if(isset($account['group_id']) && $account['group_id'] != "")
					{
						$group_id = $account['group_id'];
						$oldOrder->setCustomerGroupId($group_id);
					}
					
					
			}
		
				$oldOrder->save();
		
						$postTotal = $quote->getData('grand_total');
					if(Mage::getStoreConfig('editorder/general/reauth')) {
						if($postTotal > $preTotal) {
		
							$payment = $oldOrder->getPayment();
							$orderMethod = $payment->getMethod();
							if($orderMethod != 'free' && $orderMethod != 'checkmo' && $orderMethod != 'purchaseorder') {
		  
								if(!$payment->authorize(1, $postTotal)) {
									echo "There was an error in re-authorizing payment.";
									return $this;
								}else{
								
									$additionalInformation  = $payment->getData('additional_information');
									//echo '<pre>';print_r($additionalInformation);die;
									$payment->save();
									//$oldOrder->setTotalPaid($postTotal);
									$oldOrder->save();
								}
							}
						}
					}
						
				$this->_redirect('*/sales_order/view', array('order_id' => $oldOrder->getId()));
		
	}else{
			try {
				$this->_processActionData('save');
				if ($paymentData = $this->getRequest()->getPost('payment')) {
					$this->_getOrderCreateModel()->setPaymentData($paymentData);
					$this->_getOrderCreateModel()->getQuote()->getPayment()->addData($paymentData);
				}
	
				$order = $this->_getOrderCreateModel()
					->setIsValidate(true)
					->importPostData($this->getRequest()->getPost('order'))
					->createOrder();
	
				$this->_getSession()->clear();
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The order has been created.'));
				$this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
			}
			 catch (Mage_Payment_Model_Info_Exception $e) {
				$this->_getOrderCreateModel()->saveQuote();
				$message = $e->getMessage();
				if( !empty($message) ) {
					$this->_getSession()->addError($message);
				}
				$this->_redirect('*/*/');
			} catch (Mage_Core_Exception $e){
				$message = $e->getMessage();
				if( !empty($message) ) {
					$this->_getSession()->addError($message);
				}
				$this->_redirect('*/*/');
			}
			catch (Exception $e){
				$this->_getSession()->addException($e, $this->__('Order saving error: %s', $e->getMessage()));
				$this->_redirect('*/*/');
			}
		}
	  
		}
    /**
     * Prepare options array for info buy request
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @return array
     */
    protected function prepareEditedOptionsForRequest($item)
    {
        $newInfoOptions = array();
        if ($optionIds = $item->getOptionByCode('option_ids')) {
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                $option = $item->getProduct()->getOptionById($optionId);
                $optionValue = $item->getOptionByCode('option_'.$optionId)->getValue();

                $group = Mage::getSingleton('catalog/product_option')->groupFactory($option->getType())
                    ->setOption($option)
                    ->setQuoteItem($item);

                $newInfoOptions[$optionId] = $group->prepareOptionValueForRequest($optionValue);
            }
        }
        return $newInfoOptions;
    }
}
