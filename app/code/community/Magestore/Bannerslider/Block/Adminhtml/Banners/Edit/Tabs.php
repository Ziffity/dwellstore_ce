<?php

class Magestore_Bannerslider_Block_Adminhtml_Banners_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('banners_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('bannerslider')->__('Banner Manager'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('bannerslider')->__('Banner Information'),
          'title'     => Mage::helper('bannerslider')->__('Banner Information'),
          'content'   => $this->getLayout()->createBlock('bannerslider/adminhtml_banners_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}