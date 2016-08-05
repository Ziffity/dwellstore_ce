<?php

require_once  'Enterprise/Invitation/controllers/Customer/AccountController.php';


class DWELL_Invitation_Customer_AccountController extends Enterprise_Invitation_Customer_AccountController
{
    /**
     * DWELL-938, adding invitation setting to be picked up for SSO.
    **/
    public function preDispatch()
    {
        Mage_Core_Controller_Front_Action::preDispatch();
        
        if (!preg_match('/^(create|createpost)/i', $this->getRequest()->getActionName())) {
            $this->norouteAction();
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return;
        } else {
            // DWELL-938, adding invitation setting to be picked up for SSO.
            $session = $this->_getSession();
            $session->setData("is_invitation", 'true');
            $this->_redirect('customer/account/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return;
        }
        if (!Mage::getSingleton('enterprise_invitation/config')->isEnabledOnFront()) {
            $this->norouteAction();
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return;
        }
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('customer/account/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return;
        }

        return $this;
    }
}
