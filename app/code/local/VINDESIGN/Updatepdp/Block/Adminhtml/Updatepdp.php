<?php
class VINDESIGN_Updatepdp_Block_Adminhtml_Updatepdp extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_updatepdp';
    $this->_blockGroup = 'updatepdp';
    $this->_headerText = Mage::helper('updatepdp')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('updatepdp')->__('Add Item');
    parent::__construct();
  }
}