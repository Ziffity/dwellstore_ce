<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2013 Amasty (http://www.amasty.com)
* @package Amasty_Xnotif
*/
    
class Amasty_Xnotif_Model_Mysql4_Alert extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('productalert/stock');
    }
}