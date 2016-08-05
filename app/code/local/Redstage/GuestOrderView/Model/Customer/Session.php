<?php

class Redstage_GuestOrderView_Model_Customer_Session extends Mage_Customer_Model_Session {

	public function authenticate( Mage_Core_Controller_Varien_Action $action, $loginUrl = null ){

		if( Mage::app()->getRequest()->getActionName() == 'status' || Mage::app()->getRequest()->getActionName() == 'guest' ){
			return true;
		} else {
			return parent::authenticate( $action, $loginUrl );
		}

	}

}

?>