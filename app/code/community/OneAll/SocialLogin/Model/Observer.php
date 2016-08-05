<?php 
class OneAll_SocialLogin_Model_Observer
{
    //this is hook to Magento's event dispatched before action is run
    public function hookToControllerActionPreDispatch($observer)
    {
        //we compare action name to see if that's action for which we want to add our own event
        if($observer->getEvent()->getControllerAction()->getFullActionName() != 'customer_account_create') 
        {
            Mage::getSingleton('customer/session')->unsetData("social_data");
        }
    }
	
	}