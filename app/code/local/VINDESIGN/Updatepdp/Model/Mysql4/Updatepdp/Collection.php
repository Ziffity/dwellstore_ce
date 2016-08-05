<?php

class VINDESIGN_Updatepdp_Model_Mysql4_Updatepdp_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('updatepdp/updatepdp');
    }
}