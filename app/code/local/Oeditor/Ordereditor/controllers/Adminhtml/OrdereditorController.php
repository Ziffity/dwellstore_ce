<?php
/**
 * Magento Order Editor Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the License Version.
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 *
 * @category   Order Editor
 * @package    Oeditor_Ordereditor
 * @copyright  Copyright (c) 2010 
 * @version    0.4.1
*/
require_once 'Zend/Json/Decoder.php';
class Oeditor_Ordereditor_Adminhtml_OrdereditorController extends Mage_Adminhtml_Controller_Action
{
	private $_order;
	
	protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
//        $order = Mage::getModel('ordereditor/order')->load($id);
		$order = Mage::getModel('sales/order')->load($id);
	
        if (!$order->getId()) {
            $this->_getSession()->addError($this->__('This order no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        Mage::register('sales_order', $order);
        Mage::register('current_order', $order);
        return $order;
    }
    
	public function saveItemsAction()
	{	
		$rowTotal = 0;$rowDiscount = 0; $orderMsg = array(); $editFlag = 0; $manageInventory = 1; $productTax = 0;
		$dataArr = $_POST;
		//echo '<pre>';print_r($dataArr);die;
		$itemCount = count($dataArr['item_price']);

		$orderId = $dataArr['order_id'];
		$order = Mage::getModel('sales/order')->load($orderId);
		$oldGrandTotal = $order->getGrandTotal();
		$orderArr = $order->getData();
		
		try{
			
			foreach($dataArr['item_id'] as $key => $itemId) {

				$item = $order->getItemById($itemId);
if(isset($dataArr['remove'][$itemId]) && $dataArr['remove'][$itemId] != "") { 

					//$order->removeItem($itemId);
$item->delete();
					
				}else{

				$oldArray = array('item_price'=>$item->getPrice(), 'discount'=>$item->getDiscountAmount(), 'qty'=>$item->getQtyOrdered());

				$productTax = $productTax + $dataArr['tax'][$key];  // get the item tax
				//	$productTax = 0 ; // butt here setting tax 0 everything, so if the already added item price changes to higher/lower then the tax will set to zero,admin can set item new price inclusive tax manually
				
				$item->setTaxAmount($dataArr['tax'][$key]);

				 // and also set item tax to zero so that, tax amount that is calculated(by customer-for old product while purchase), will not show in the item list
				//$item->setTaxPercent($dataArr['tax_percent'][$key]);
				 // and also set item tax percentage to zero so that, tax amount that is calculated(by customer-for old product while purchase), will not show in the item list
				
				$item->setPrice($dataArr['item_price'][$key]); 
				$item->setBasePrice($dataArr['item_price'][$key]);
				$item->setBaseOriginalPrice($dataArr['item_price'][$key]);
				$item->setOriginalPrice($dataArr['item_price'][$key]);
				
				$item->setBaseRowTotal($dataArr['item_price'][$key] * $dataArr['qty'][$key]);
				$item->setRowTotal($dataArr['item_price'][$key] * $dataArr['qty'][$key]); //new

				$item->setRowTotalInclTax($dataArr['item_price'][$key] * $dataArr['qty'][$key]); //new
				$item->setRowTotalInclTax($dataArr['item_price'][$key] * $dataArr['qty'][$key]); //new
				 
				
				if(isset($dataArr['discount'][$key]) && $dataArr['discount'][$key] != 0) {
					$item->setDiscountAmount($dataArr['discount'][$key]);
					$item->setBaseDiscountAmount($dataArr['discount'][$key]);
				}else{$item->setDiscountAmount(0);}
				
				if($dataArr['qty'][$key])
				{
					$item->setQtyOrdered($dataArr['qty'][$key]);
				}
				$item->save();
				
		
		
				 $rowTotal =  $rowTotal + $item->getRowTotal();
				$rowDiscount = $rowDiscount +  $item->getDiscountAmount() ;
				 
				$newArray = array('item_price'=>$item->getPrice(), 'discount'=>$item->getDiscountAmount(), 'qty'=>$item->getQtyOrdered());
				if($newArray['item_price'] != $oldArray['item_price'] || $newArray['discount'] != $oldArray['discount'] || $newArray['qty'] != $oldArray['qty']) 
				{
					 
					if($newArray['item_price'] != $oldArray['item_price']) {
						 
					}
					if($newArray['discount'] != $oldArray['discount']) {
						 
					}
					if($newArray['qty'] != $oldArray['qty']) {
						 		
					}
				
				}

			}
		}
		
				$order->setSubtotal($rowTotal);
				$order->setDiscountAmount('-'.$rowDiscount); 

				$order->setBaseSubtotal($rowTotal);
				//$order->setBaseSubtotalInclTax($rowTotal+$productTax);
				//$order->setSubtotalInclTax($rowTotal+$productTax);
				
				$order->setBaseSubtotalInclTax($rowTotal);
				$order->setSubtotalInclTax($rowTotal);
				
				$order->setBaseGrandTotal($order->getShippingAmount()+$rowTotal+$productTax-$rowDiscount);
				
				/* set directly total order tax amount,so order exclusive grand total will automatically minus this tax from order inclusive grand total it is the amount tha will show the total tax summary (+)(shipping+product tax) */ 
				$order->setTaxAmount($productTax); 
				$order->setBaseTaxAmount($productTax); 


	$resource = Mage::getSingleton('core/resource');
    $writeConnection = $resource->getConnection('core_write');
    $table = $resource->getTableName('sales/order_tax');
	$orderIdTax = $order->getId();
	$query = "UPDATE {$table} SET amount = '{$productTax}',base_amount = '{$productTax}',base_real_amount = '{$productTax}' WHERE order_id  = "
             . (int)$orderIdTax;
    $writeConnection->query($query);
	
				$order->setGrandTotal($order->getShippingAmount()+$rowTotal+$productTax-$rowDiscount);
				$order->save();	
				
				$newTotal = $order->getGrandTotal();
				
					if(Mage::getStoreConfig('editorder/general/reauth')) {
						if($newTotal > $oldGrandTotal) {
			
								$payment = $order->getPayment();
								$orderMethod = $payment->getMethod();
								if($orderMethod != 'free' && $orderMethod != 'checkmo' && $orderMethod != 'purchaseorder') {
			 
									if(!$payment->authorize(1, $newTotal)) {
										echo "There was an error in re-authorizing payment.";
										return $this;
									}else{
										$additionalInformation  = $payment->getData('additional_information');
										$payment->save();
										//$order->setTotalPaid($postTotal);
										$order->save();
									}
								}
							}
						}
						
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The order has been updated successfully.'));
				echo "Successfully updated.";
				return $this;
		}
		catch(Exception $e){
				Mage::getSingleton('adminhtml/session')->addException($e, $this->__('Order saving error: %s', $e->getMessage()));
				$path = Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/view/order_id/".$orderId);
				$this->_redirectUrl($path);
			}
		
	}
	
	private function _loadOrder($orderId) {
		$this->_order = Mage::getModel('sales/order')->load($orderId);
		if(!$this->_order->getId()) return false;
		return true;
	}
	
	public function saveinvoicestatusAction() {
		$field = $this->getRequest()->getParam('field');
		$invoiceId = $this->getRequest()->getParam('invoice_id');
		$value = $this->getRequest()->getPost('value');
 
		if (!empty($field) && !empty($invoiceId)) {
			$invoice = Mage::getModel('sales/order_invoice')
                    ->load($invoiceId);
			$invoiceState = $invoice->setState($value);
			$invoice->save();

			$statuses = Mage::getModel('sales/order_invoice')->getStates();
			$invoiceState = $invoice->getState();
			if(isset($invoiceState))
			echo $invoiceStateLabel = $statuses[$invoiceState];
			else echo 'error in saving..';
		}
	}
	
	public function saveAction() {
		$field = $this->getRequest()->getParam('field');
		$type = $this->getRequest()->getParam('type');
		$orderId = $this->getRequest()->getParam('order');
		$value = $this->getRequest()->getPost('value');
		if (!empty($field) && !empty($type) && !empty($orderId)) {
			if(!empty($value)) {
				if(!$this->_loadOrder($orderId)) {
					$this->getResponse()->setBody($this->__('error: missing order number'));
				}
				$res = $this->_editAddress($type,$field,$value);
				if($res !== true) {
					$this->getResponse()->setBody($this->__('error: '.$res));
				} else {
						
						if($field == "order_status"){
							$statuses = Mage::getSingleton('sales/order_config')->getStatuses();
							foreach($statuses as $key=>$keyValue)
							{
								if($key == $value) { $this->getResponse()->setBody($keyValue);break;} 
							}
							
						}
						
						else{$this->getResponse()->setBody($value); }
				}
			} else {
				$this->getResponse()->setBody($this->__('error: value required'));
			}
		} else {
			$this->getResponse()->setBody('undefined error');
		}
	}
  

	private function _editAddress($type,$field,$value) {
  //echo $type.'='.$field.'='.$value;die;
		if($type == "bill") {
			  $address = $this->_order->getBillingAddress();
			 
			$addressSet = 'setBillingAddress';
		} elseif($type == "ship") {
			$address = $this->_order->getShippingAddress();
			$addressSet = 'setShippingAddress';
		} elseif($type == "cemail") {
				$this->_order->setCustomerEmail($value)->save();
				return true;
		} elseif($type == "cust_name") {

				$explodeName = explode(" ",$value);
				if(isset($explodeName[0]) && $explodeName[0] != ""){ $firstName = $explodeName[0]; $this->_order->setCustomerFirstname($firstName)->save();}
				if(isset($explodeName[1]) && $explodeName[1] != ""){ $lastName = $explodeName[1]; $this->_order->setCustomerLastname($lastName)->save();}
			
				
				return true;
		} elseif($type == "edit_ord") {
				$this->_order->setStatus($value)->save();
				$item = Mage::getResourceModel('sales/order_status_collection')
                  ->joinStates()
                  ->addFieldToFilter('main_table.status', $value)
                  ->getFirstItem();
			    $this->_order->setState($item->getState())->save();
 
				return true;
		}
		
		elseif($type == "edit_customer_group") {

			$this->_order->setCustomerGroupId($value)->save();
			
			$group = Mage::getModel('customer/group')->load($value);
      		echo $value = $group->getCode();die;
			return true;
			
		}
		
		else {
			return 'type not defined';
		}

		$updated = false;
    	$fieldGet = 'get'.ucwords($field);
    	$fieldSet = 'set'.ucwords($field);


    	if($address->$fieldGet() != $value) {
 
    		if($field == 'country') {
    			$fieldSet = 'setCountryId';
    			$countries = array_flip(Mage::app()->getLocale()->getCountryTranslationList());
    			if(isset($countries[$value])) {
    				$value = $countries[$value];
    			} else {
    				return 'country not found';
    			}
    		}
    		if(substr($field,0,6) == 'street') {
    			$i = substr($field,6,1);
    			if(!is_numeric($i))
    				$i = 1;
    			$valueOrg = $value;
    			$value = array();
    			for($n=1;$n<=4;$n++) {
    				if($n != $i) {
	    				$value[] = $address->getStreet($n);
    				} else {
    					$value[] = $valueOrg;
    				}
    			}
    			$fieldSet = 'setStreet';
    		}
    		//update field and set as updated
    		$address->$fieldSet($value);
    		$updated = true;
    	}

		if($updated) {
//			$this->_order->setStatus($value)->save();
 if($field == "firstname") {
	$this->_order->setFirstName($value)->save();
	return true;
}
 if($field == "lastname") {
	$this->_order->setLastName($value)->save();
	return true;
}

 if($field == "street1") {
	$this->_order->setStreet1($value)->save();
	return true;
}

 if($field == "street2") {
	$this->_order->setStreet2($value)->save();
	return true;
}

 if($field == "street3") {
	$this->_order->setStreet3($value)->save();
	return true;
}
 if($field == "street4") {
	$this->_order->setStreet4($value)->save();
	return true;
}

 if($field == "city") {
	$this->_order->setCity($value)->save();
	return true;
}
 if($field == "region") {
	$this->_order->setRegion($value)->save();
	return true;
}
 if($field == "postcode") {
	$this->_order->setPostcode($value)->save();
	return true;
}
 if($field == "country") {
	$this->_order->setCountry($value)->save();
	return true;
}
 if($field == "telephone") {
	$this->_order->setTelephone($value)->save();
	return true;
}
 if($field == "fax") {
	$this->_order->setFax($value)->save();
	return true;
}

			$this->_order->$addressSet($address);
        	$this->_order->save();
		}
		return true;
	}
	
	public function deleteInvoiceShipCreditMemoAction()
	{
			$orderId = $_REQUEST['order_id'];
			
			$countDeleteOrder = 0;
	        $countDeleteInvoice = 0;
	        $countDeleteShipment = 0;
	        $countDeleteCreditmemo = 0;

			//$order = Mage::getModel('ordereditor/order')->load($id);
			$order = Mage::getModel('sales/order')->load($orderId);
			$coreResource = Mage::getSingleton('core/resource');
        	$write = $coreResource->getConnection('core_write');
		
			if ($order->hasInvoices()) {
				$invoices = Mage::getResourceModel('sales/order_invoice_collection')->setOrderFilter($orderId)->load();
				foreach($invoices as $invoice){
					$invoice = Mage::getModel('sales/order_invoice')->load($invoice->getId());
					$invoice->delete();
					$write->query("DELETE FROM `".$coreResource->getTableName('sales_flat_invoice')."` WHERE `order_id`=".$orderId);
					$write->query("DELETE FROM `".$coreResource->getTableName('sales_flat_invoice_grid')."` WHERE `order_id`=".$orderId);
					$countDeleteInvoice++;
				}
			}
			
			if ($order->hasShipments()) {
				$shipments = Mage::getResourceModel('sales/order_shipment_collection')->setOrderFilter($orderId)->load();
				foreach($shipments as $shipment){
					$shipment = Mage::getModel('sales/order_shipment')->load($shipment->getId());
					$shipment->delete();
					$write->query("DELETE FROM `".$coreResource->getTableName('sales_flat_shipment')."` WHERE `order_id`=".$orderId);            
					$write->query("DELETE FROM `".$coreResource->getTableName('sales_flat_shipment_grid')."` WHERE `order_id`=".$orderId);            
					$countDeleteShipment++;
				}
			}
			
			if ($order->hasCreditmemos()) {
				$creditmemos = Mage::getResourceModel('sales/order_creditmemo_collection')->setOrderFilter($orderId)->load();
				foreach($creditmemos as $creditmemo){
					$creditmemo = Mage::getModel('sales/order_creditmemo')->load($creditmemo->getId());
					$creditmemo->delete();
					$countDeleteCreditmemo++;
				}
			}
			
			foreach ($order->getAllItems() as $item) 
			{
				$item['qty_invoiced'] = 0;
				$item['row_invoiced'] = 0;
				$item['base_row_invoiced'] = 0;
				$item['tax_invoiced'] = 0;
				$item['base_tax_invoiced'] = 0;
				$item['discount_invoiced'] = 0;
				$item['base_discount_invoiced'] = 0;
				
				$item['qty_shipped'] = 0;
				$item->save();
			}
			$order->setStatus('pending');
			$order->setState('new');
			
			$order->setBaseShippingInvoiced(0);
			$order->setBaseSubtotalInvoiced(0);
			$order->setBaseTaxInvoiced(0);
			$order->setBaseTotalInvoiced(0);
			$order->setBaseTotalInvoicedCost(0);
			$order->setDiscountInvoiced(0);
			$order->setShippingInvoiced(0);
			$order->setSubtotalInvoiced(0);
			$order->setTaxInvoiced(0);
			$order->setTotalInvoiced(0);
			
			$order->setBaseTotalPaid(0);
			$order->setTotalPaid(0);
			$order->save();
		
		   if ($countDeleteInvoice > 0) {
				$this->_getSession()->addSuccess($this->__('%s invoice is successfully deleted.', $countDeleteInvoice));
			} 
			
			$path = Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/view/order_id/".$orderId);
			$this->_redirectUrl($path);
	}
	
	public function saveshippingAction()
	{
		//	$postData = $_REQUEST;
		$postData = $_POST;

		
			$orderId = $postData['order_id'];
			
			$order = Mage::getModel('sales/order')->load($orderId);

			$oldShippingAmount = $order->getShippingAmount();
			$oldGrandTotal = $order->getGrandTotal();
			
			try{
//			upShipPrice = (upShipPrice < 0) ? upShipPrice * -1 : upShipPrice;
				if(isset($postData['ship_price']) && $postData['ship_price'] != '') 
				{
					$postData['ship_price'] = ($postData['ship_price'] < 0) ? $postData['ship_price'] * -1 : $postData['ship_price'];
					
						$order->setShippingAmount($postData['ship_price']);
						//$order->setShippingTaxAmount($postData['ship_tax']);
						
						//$inclTax = $postData['ship_price']+$postData['ship_tax'];
						
						$order->setShippingInclTax($postData['ship_price']);
						$order->setBaseShippingInclTax($postData['ship_price']);
				  
					if(isset($postData['custom_shipping_method']) && $postData['custom_shipping_method'] != '') 
					{
						 $newMethod = strtolower($postData['custom_shipping_method']);
						 if($newMethod == 'other' || $newMethod == "none"){$newMethod = 'freeshipping';}
						 
						$order->setShippingMethod($newMethod);
					
						$order->setShippingDescription($postData['custom_shipping_method']." - ".$postData['custom_name']);
					}			
	
					$newShippingAmount = $order->getShippingAmount();
					$newTotal = $oldGrandTotal + $newShippingAmount - $oldShippingAmount;
	
					$order->setBaseGrandTotal($newTotal);

 
					$order->setGrandTotal($newTotal);
					$order->save();
					
					if(Mage::getStoreConfig('editorder/general/reauth')) {
						if($newTotal > $oldGrandTotal) {
			
								$payment = $order->getPayment();
								$orderMethod = $payment->getMethod();
								if($orderMethod != 'free' && $orderMethod != 'checkmo' && $orderMethod != 'purchaseorder') {
			 
									if(!$payment->authorize(1, $newTotal)) {
										echo "There was an error in re-authorizing payment.";
										return $this;
									}else{
										$additionalInformation  = $payment->getData('additional_information');
										$payment->save();
										//$order->setTotalPaid($postTotal);
										$order->save();
									}
								}
							}
						}
					
					Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The shipping has been updated successfully.'));
					echo "Successfully updated.";
					return $this;
				/*
					Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The shipping has been updated successfully.'));
					$path = Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/view/order_id/".$orderId);
					$this->_redirectUrl($path);
				*/	
			}
			else
			{
				Mage::getSingleton('adminhtml/session')->addError($this->__('Please enter Shipping Price.'));
				$path = Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/view/order_id/".$orderId);
				$this->_redirectUrl($path);
			}
			
			}catch(Exception $e){
				Mage::getSingleton('adminhtml/session')->addException($e, $this->__('Order saving error: %s', $e->getMessage()));
				$path = Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/view/order_id/".$orderId);
				$this->_redirectUrl($path);
			}

	}
}