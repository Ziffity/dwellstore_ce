<?php
class DWELL_Signup_Model_Mysql4_Signup extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("signup/signup", "dwell_signup_id");
    }
}