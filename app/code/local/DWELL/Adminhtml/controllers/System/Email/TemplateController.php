<?php

/**
 * Override Mage Core Admin System Email Template Controller to disable New Relic RUM
 * Drupal SSO implementation.
 */

require_once 'Mage/Adminhtml/controllers/System/Email/TemplateController.php';

class DWELL_Adminhtml_System_Email_TemplateController extends Mage_Adminhtml_System_Email_TemplateController  {

    /**
     * Load email template from request
     *
     * @param string $idFieldName
     * @return Mage_Adminhtml_Model_Email_Template $model
     */
    protected function _initTemplate($idFieldName = 'template_id')
    {  
    	if (extension_loaded('newrelic')) {
    	  newrelic_disable_autorum();
    	}
    	
    	return parent::_initTemplate($idFieldName);
      
    }

    
}
