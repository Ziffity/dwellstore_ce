<?php

require_once( 'Mage/Sales/controllers/OrderController.php' );

class Redstage_GuestOrderView_OrderController extends Mage_Sales_OrderController
{

	protected function _viewGuestAction(){
		if (!$this->_loadGuestValidOrder()) {
			return;
		}
		$this->loadLayout();
		$this->_initLayoutMessages('core/session');
		$this->renderLayout();
	}

	protected function _canGuestViewOrder($order){
		$availableStates = Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates();
		if (
			$order->getId()
			&& $order->getCustomerEmail() == $this->getRequest()->getParam('email')
			&& in_array($order->getState(), $availableStates, $strict = true)
		) {
			return true;
		}
		return false;
	}

	protected function _loadGuestValidOrder($orderId = null)
	{
		if (null === $orderId) {
			$orderId = (string) $this->getRequest()->getParam('order_id');
		}
		
		$no_order_error_message = "We're sorry, we could not locate your order. <br />Please contact <a href=\"/customer-service/contact-us\">customer service</a> and we'll be happy to assist you.";
		
		if (!$orderId) {
			Mage::getSingleton('core/session')->addError($no_order_error_message);
			$this->_redirect('sales/order/guest');
			return false;
		}

		$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

		if ($this->_canGuestViewOrder($order)) {
			Mage::register('current_order', $order);
			return true;
		}
		else {
			//Mage::getSingleton('core/session')->addError($no_order_error_message);
			$this->_redirect('sales/order/guest');
		}
		return false;
	}

	public function statusAction()
	{
		$this->_viewGuestAction();
	}

	public function guestAction(){
		//$this->loadLayout();
		//$this->_initLayoutMessages('core/session');
		//$this->renderLayout();
		echo "We're sorry, we could not locate your order. <br />Please contact <a style='color:#ff0000;' href=\"/customer-service/contact-us\">customer service</a> and we'll be happy to assist you.";
	}

}