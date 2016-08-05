<?php

class VINDESIGN_Updatepdp_Block_Adminhtml_Updatepdp_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('updatepdp_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('updatepdp')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('updatepdp')->__('Item Information'),
          'title'     => Mage::helper('updatepdp')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('updatepdp/adminhtml_updatepdp_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}