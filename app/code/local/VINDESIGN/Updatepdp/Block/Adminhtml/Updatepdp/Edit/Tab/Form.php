<?php

class VINDESIGN_Updatepdp_Block_Adminhtml_Updatepdp_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('updatepdp_form', array('legend'=>Mage::helper('updatepdp')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('updatepdp')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('updatepdp')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('updatepdp')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('updatepdp')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('updatepdp')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('updatepdp')->__('Content'),
          'title'     => Mage::helper('updatepdp')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getUpdatepdpData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getUpdatepdpData());
          Mage::getSingleton('adminhtml/session')->setUpdatepdpData(null);
      } elseif ( Mage::registry('updatepdp_data') ) {
          $form->setValues(Mage::registry('updatepdp_data')->getData());
      }
      return parent::_prepareForm();
  }
}