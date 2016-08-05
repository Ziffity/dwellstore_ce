<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2012 Amasty (http://www.amasty.com)
* @package Amasty_Xnotif
*/
class Amasty_Xnotif_Block_Adminhtml_Stock extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_controller = 'adminhtml_stock';
        $this->_blockGroup = 'amxnotif';
        $this->_headerText = Mage::helper('amxnotif')->__('Stock Alerts');
        $this->_removeButton('add'); 
    }
}